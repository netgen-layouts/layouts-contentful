<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Entity;

use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Space;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Throwable;
use function str_starts_with;
use function trigger_error;
use const E_USER_ERROR;

/**
 * @final
 */
class ContentfulEntry implements RouteReferrersInterface, JsonSerializable
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
    private $isPublished = false;

    /**
     * @var bool
     */
    private $isDeleted = false;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Symfony\Component\Routing\Route>
     */
    private $routes;

    /**
     * Original Contentful entry.
     *
     * @var \Contentful\Delivery\Resource\Entry
     */
    private $remoteEntry;

    public function __construct(?Entry $remoteEntry = null)
    {
        $this->routes = new ArrayCollection();

        if ($remoteEntry instanceof Entry) {
            $this->setRemoteEntry($remoteEntry);
        }
    }

    /**
     * @param mixed[] $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (!str_starts_with($name, 'get')) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }

        $ret = null;

        try {
            /** @var callable $callable */
            $callable = [$this->remoteEntry, $name];
            $ret = $callable();
        } catch (Throwable $t) {
        }

        return $ret;
    }

    public function has(string $name, ?string $locale = null, bool $checkLinksAreResolved = true): bool
    {
        return $this->remoteEntry->has($name, $locale, $checkLinksAreResolved);
    }

    /**
     * @return mixed
     */
    public function get(string $name, ?string $locale = null, bool $resolveLinks = true)
    {
        return $this->remoteEntry->get($name, $locale, $resolveLinks);
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
     * @param \Doctrine\Common\Collections\Collection<int, \Symfony\Component\Routing\Route> $routes
     */
    public function setRoutes(Collection $routes): void
    {
        $this->routes = $routes;
    }

    /**
     * @return \Symfony\Component\Routing\Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes->getValues();
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
    public function getRemoteEntry(): Entry
    {
        return $this->remoteEntry;
    }

    /**
     * Returns the remote entry revision.
     */
    public function getRevision(): int
    {
        return $this->remoteEntry->getSystemProperties()->getRevision();
    }

    /**
     * Returns the date when the remote entry was last updated.
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->remoteEntry->getSystemProperties()->getUpdatedAt();
    }

    /**
     * Returns the date when the remote entry was created.
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->remoteEntry->getSystemProperties()->getCreatedAt();
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->remoteEntry->jsonSerialize();
    }

    /**
     * Sets the remote entry.
     */
    public function setRemoteEntry(Entry $remoteEntry): void
    {
        $this->remoteEntry = $remoteEntry;
        $this->id = $this->remoteEntry->getSpace()->getId() . '|' . $this->remoteEntry->getId();

        $nameField = $this->remoteEntry->getContentType()->getDisplayField();
        if ($nameField === null) {
            return;
        }

        /** @var callable $callable */
        $callable = [$remoteEntry, 'get' . $nameField->getId()];
        $this->name = $callable();
    }

    /**
     * Revives the remote entry from provided client.
     */
    public function reviveRemoteEntry(ClientInterface $client): void
    {
        if (!$client instanceof JsonDecoderClientInterface) {
            return;
        }

        /** @var \Contentful\Delivery\Resource\Entry $remoteEntry */
        $remoteEntry = $client->parseJson($this->json);
        $this->id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();

        $nameField = $remoteEntry->getContentType()->getDisplayField();
        if ($nameField === null) {
            return;
        }

        /** @var callable $callable */
        $callable = [$remoteEntry, 'get' . $nameField->getId()];
        $this->name = $callable();

        $this->remoteEntry = $remoteEntry;
    }
}
