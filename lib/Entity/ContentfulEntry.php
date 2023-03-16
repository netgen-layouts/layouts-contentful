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
use RuntimeException;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Symfony\Component\Routing\Route;
use Throwable;

use function method_exists;
use function sprintf;

/**
 * @final
 */
class ContentfulEntry implements RouteReferrersInterface, JsonSerializable
{
    private string $id;

    private string $name;

    private string $json;

    private bool $isPublished = false;

    private bool $isDeleted = false;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Symfony\Component\Routing\Route>
     */
    private Collection $routes;

    /**
     * Original Contentful entry.
     */
    private Entry $remoteEntry;

    public function __construct(?Entry $remoteEntry = null)
    {
        $this->routes = new ArrayCollection();

        if ($remoteEntry instanceof Entry) {
            $this->setRemoteEntry($remoteEntry);
        }
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->remoteEntry, $name)) {
            throw new RuntimeException(
                sprintf('Call to undefined method %s::%s', $this->remoteEntry::class, $name),
            );
        }

        try {
            return ($this->remoteEntry->{$name}(...))();
        } catch (Throwable) {
            return null;
        }
    }

    public function has(string $name, ?string $locale = null, bool $checkLinksAreResolved = true): bool
    {
        return $this->remoteEntry->has($name, $locale, $checkLinksAreResolved);
    }

    public function get(string $name, ?string $locale = null, bool $resolveLinks = true): mixed
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

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function removeRoute(Route $route): void
    {
        $this->routes->removeElement($route);
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

        $methodName = 'get' . $nameField->getId();
        $this->name = ($remoteEntry->{$methodName}(...))();
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

        $methodName = 'get' . $nameField->getId();
        $this->name = ($remoteEntry->{$methodName}(...))();

        $this->remoteEntry = $remoteEntry;
    }
}
