<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Entity;

use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Space;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;
use RuntimeException;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Symfony\Component\Routing\Route;
use Throwable;

use function method_exists;
use function sprintf;

final class ContentfulEntry implements RouteReferrersInterface, JsonSerializable
{
    /**
     * Returns the entry ID.
     */
    public string $id;

    /**
     * Returns the entry name.
     */
    public string $name;

    /**
     * Returns the entry JSON representation.
     */
    public string $json;

    /**
     * Returns if the entry is published.
     */
    public bool $isPublished = false;

    /**
     * Returns if the entry is deleted.
     */
    public bool $isDeleted = false;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Symfony\Component\Routing\Route>
     */
    public Collection $routes;

    /**
     * Returns the remote entry.
     */
    public Entry $remoteEntry {
        set {
            $this->remoteEntry = $value;
            $this->id = $this->remoteEntry->getSpace()->getId() . '|' . $this->remoteEntry->getId();

            $nameField = $this->remoteEntry->getContentType()->getDisplayField();
            if ($nameField === null) {
                return;
            }

            $methodName = 'get' . $nameField->getId();
            $this->name = ($value->{$methodName}(...))();
        }
    }

    /**
     * Returns the remote entry revision.
     */
    public int $revision {
        get => $this->remoteEntry->getSystemProperties()->getRevision();
    }

    /**
     * Returns the date when the remote entry was last updated.
     */
    public DateTimeImmutable $updatedAt {
        get => DateTimeImmutable::createFromInterface($this->remoteEntry->getSystemProperties()->getUpdatedAt());
    }

    /**
     * Returns the date when the remote entry was created.
     */
    public DateTimeImmutable $createdAt {
        get => DateTimeImmutable::createFromInterface($this->remoteEntry->getSystemProperties()->getCreatedAt());
    }

    /**
     * Returns the remote entry space.
     */
    public Space $space {
        get => $this->remoteEntry->getSpace();
    }

    /**
     * Returns the remote entry content type.
     */
    public ContentType $contentType {
        get => $this->remoteEntry->getContentType();
    }

    public function __construct(?Entry $remoteEntry = null)
    {
        $this->routes = new ArrayCollection();

        if ($remoteEntry instanceof Entry) {
            $this->remoteEntry = $remoteEntry;
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

    public function getRoutes(): iterable
    {
        return $this->routes;
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
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->remoteEntry->jsonSerialize();
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
