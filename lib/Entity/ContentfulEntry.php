<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Entity;

use Contentful\Delivery\Client;
use Contentful\Delivery\ContentType;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\EntryInterface;
use Contentful\Delivery\Space;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;

/**
 * @final
 */
class ContentfulEntry implements RouteReferrersInterface, EntryInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $json;

    /**
     * @var bool
     */
    private $isPublished;

    /**
     * @var bool
     */
    private $isDeleted;

    /**
     * @var \Symfony\Cmf\Component\Routing\RouteObjectInterface[]|\Doctrine\Common\Collections\ArrayCollection
     */
    private $routes;

    /**
     * Original Contentful entry.
     *
     * @var \Contentful\Delivery\DynamicEntry
     */
    private $remoteEntry;

    public function __construct(?DynamicEntry $remoteEntry = null)
    {
        $this->routes = new ArrayCollection();

        if ($remoteEntry instanceof DynamicEntry) {
            $this->setRemoteEntry($remoteEntry);
        }
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (mb_strpos($name, 'get') !== 0) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }

        $ret = null;

        try {
            $ret = call_user_func([$this->remoteEntry, $name]);
        } catch (Exception $e) {
        }

        return $ret;
    }

    /**
     * Returns the entry ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets the entry ID.
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the entry name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the entry name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the entry JSON representation.
     */
    public function getJson(): string
    {
        return $this->json;
    }

    /**
     * Sets the JSON representation of the entry.
     */
    public function setJson(string $json): self
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Returns if the entry is published.
     */
    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    /**
     * Sets if the entry is published.
     */
    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Returns if the entry is deleted.
     */
    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * Sets if the entry is deleted.
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Sets the entry routes.
     *
     * @param \Symfony\Cmf\Component\Routing\RouteObjectInterface[]|\Doctrine\Common\Collections\ArrayCollection $routes
     */
    public function setRoutes(ArrayCollection $routes): void
    {
        $this->routes = $routes;
    }

    public function getRoutes(): ArrayCollection
    {
        return $this->routes;
    }

    public function addRoute($route): self
    {
        $this->routes[] = $route;

        return $this;
    }

    public function removeRoute($route): self
    {
        $this->routes->removeElement($route);

        return $this;
    }

    /**
     * Returns the remote entry.
     */
    public function getRemoteEntry(): EntryInterface
    {
        return $this->remoteEntry;
    }

    /**
     * Returns the remote entry revision.
     */
    public function getRevision(): int
    {
        return $this->remoteEntry->getRevision();
    }

    /**
     * Returns the date when the remote entry was last updated.
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->remoteEntry->getUpdatedAt();
    }

    /**
     * Returns the date when the remote entry was created.
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->remoteEntry->getCreatedAt();
    }

    /**
     * Returns the remote entry space.
     */
    public function getSpace(): Space
    {
        return $this->remoteEntry->getSpace();
    }

    /**
     * Returns the remote entry content type.
     */
    public function getContentType(): ContentType
    {
        return $this->remoteEntry->getContentType();
    }

    public function jsonSerialize()
    {
        return $this->remoteEntry->jsonSerialize();
    }

    /**
     * Sets the remote entry.
     */
    public function setRemoteEntry(DynamicEntry $remoteEntry): void
    {
        $this->remoteEntry = $remoteEntry;
        $this->id = $this->remoteEntry->getSpace()->getId() . '|' . $this->remoteEntry->getId();

        $nameField = $this->remoteEntry->getContentType()->getDisplayField();
        $this->name = call_user_func([$this->remoteEntry, 'get' . $nameField->getId()]);
    }

    /**
     * Revives the remote entry from provided client.
     */
    public function reviveRemoteEntry(Client $client): void
    {
        /** @var \Contentful\Delivery\DynamicEntry $remoteEntry */
        $remoteEntry = $client->reviveJson($this->json);
        $this->id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();

        $nameField = $remoteEntry->getContentType()->getDisplayField();

        $this->name = call_user_func([$remoteEntry, 'get' . $nameField->getId()]);
        $this->remoteEntry = $remoteEntry;
    }
}
