<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Service;

use Contentful\Delivery\Client;
use Contentful\Delivery\ContentType;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\EntryInterface;
use Contentful\Delivery\Query;
use Contentful\Delivery\Space;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Contentful\ResourceArray;
use Doctrine\ORM\EntityManagerInterface;
use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Exception\NotFoundException;
use Netgen\BlockManager\Contentful\Exception\RuntimeException;
use Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Filesystem\Filesystem;

final class Contentful
{
    /**
     * @var array
     */
    private $clientsConfig;

    /**
     * @var \Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface
     */
    private $entrySlugger;

    /**
     * @var \Contentful\Delivery\Client
     */
    private $defaultClient;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $cacheDir;

    public function __construct(
        array $clientsConfig,
        EntrySluggerInterface $entrySlugger,
        Client $defaultClient,
        EntityManagerInterface $entityManager,
        Filesystem $fileSystem,
        string $cacheDir
    ) {
        $this->clientsConfig = $clientsConfig;
        $this->entrySlugger = $entrySlugger;
        $this->defaultClient = $defaultClient;
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        $this->cacheDir = $cacheDir;
    }

    /**
     * Returns the Contentful client with provided name.
     *
     * @throws \Netgen\BlockManager\Contentful\Exception\RuntimeException If client with provided name does not exist
     */
    public function getClientByName(string $name): Client
    {
        if (!isset($this->clientsConfig[$name])) {
            throw new RuntimeException(sprintf('Contentful client with "%s" name does not exist.', $name));
        }

        return $this->clientsConfig[$name]['service'];
    }

    /**
     * Returns the Contentful space with provided client name.
     */
    public function getSpaceByClientName(string $name): Space
    {
        return $this->clientsConfig[$name]['space'];
    }

    /**
     * Returns the Contentful client which serves the space with provided ID.
     *
     * If no client is found, null is returned.
     */
    public function getClientBySpaceId(string $spaceId): ?Client
    {
        foreach ($this->clientsConfig as $clientConfig) {
            if ($clientConfig['space'] === $spaceId) {
                return $clientConfig['service'];
            }
        }

        return null;
    }

    /**
     * Returns all configured clients.
     *
     * @return \Contentful\Delivery\Client[]
     */
    public function getClients(): array
    {
        $clients = [];

        foreach ($this->clientsConfig as $clientConfig) {
            $clients[] = $clientConfig['service'];
        }

        return $clients;
    }

    /**
     * Returns the content type with specified ID.
     *
     * If no content type is found, null is returned.
     */
    public function getContentType(string $id): ?ContentType
    {
        foreach ($this->clientsConfig as $clientConfig) {
            /** @var \Contentful\Delivery\Client $client */
            $client = $clientConfig['service'];

            foreach ($client->getContentTypes()->getItems() as $contentType) {
                /** @var \Contentful\Delivery\ContentType $contentType */
                if ($contentType->getId() === $id) {
                    return $contentType;
                }
            }
        }

        return null;
    }

    /**
     * Returns names of all configured clients.
     *
     * @return string[]
     */
    public function getClientsNames(): array
    {
        return array_keys($this->clientsConfig);
    }

    /**
     * Returns the Contentful entry with provided ID.
     *
     * @throws \Netgen\BlockManager\Contentful\Exception\NotFoundException If entry could not be loaded
     */
    public function loadContentfulEntry(string $id): ContentfulEntry
    {
        $idList = explode('|', $id);
        if (count($idList) !== 2) {
            throw new NotFoundException(
                sprintf(
                    'Item ID %s not valid.',
                    $id
                )
            );
        }

        $client = $this->getClientBySpaceId($idList[0]);

        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->reviveRemoteEntry($client);
        } else {
            $remoteEntry = $client->getEntry($idList[1]);

            if (!$remoteEntry instanceof EntryInterface) {
                throw new NotFoundException(
                    sprintf(
                        'Entry with ID %s not found.',
                        $id
                    )
                );
            }

            $contentfulEntry = $this->buildContentfulEntry($remoteEntry, $id);
        }

        if ($contentfulEntry->getIsDeleted()) {
            throw new NotFoundException(
                sprintf(
                    'Entry with ID %s deleted.',
                    $id
                )
            );
        }

        return $contentfulEntry;
    }

    /**
     * Returns the list of Contentful entries.
     */
    public function getContentfulEntries(int $offset = 0, ?int $limit = null, ?Client $client = null, ?Query $query = null): array
    {
        $client = $client ?? $this->defaultClient;
        $query = $query ?? new Query();

        $query->setSkip($offset);
        if ($limit !== null) {
            $query->setLimit($limit);
        }

        return $this->buildContentfulEntries($client->getEntries($query), $client);
    }

    /**
     * Returns the count of Contentful entries.
     */
    public function getContentfulEntriesCount(?Client $client = null, ?Query $query = null): int
    {
        $client = $client ?? $this->defaultClient;

        return count($client->getEntries($query));
    }

    /**
     * Searches for Contentful entries.
     */
    public function searchContentfulEntries(string $searchText, int $offset = 0, int $limit = 25, ?Client $client = null): array
    {
        $client = $client ?? $this->defaultClient;

        $query = new Query();
        $query->setLimit($limit);
        $query->setSkip($offset);
        $query->where('query', $searchText);

        return $this->buildContentfulEntries($client->getEntries($query), $client);
    }

    /**
     * Returns the count of searched Contentful entries.
     */
    public function searchContentfulEntriesCount(string $searchText, ?Client $client = null): int
    {
        $client = $client ?? $this->defaultClient;

        $query = new Query();
        $query->where('query', $searchText);

        return count($client->getEntries($query));
    }

    /**
     * Returns the list of clients and content types for usage in Symfony Forms.
     */
    public function getClientsAndContentTypesAsChoices(): array
    {
        $clientsAndContentTypes = [];

        foreach ($this->clientsConfig as $clientName => $clientConfig) {
            /** @var \Contentful\Delivery\Client $client */
            $client = $clientConfig['service'];

            $clientsAndContentTypes[$client->getSpace()->getName()] = $clientName;
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                $clientsAndContentTypes['>  ' . $contentType->getName()] = $clientName . '|' . $contentType->getId();
            }
        }

        return $clientsAndContentTypes;
    }

    /**
     * Returns the list of spaces for usage in Symfony Forms.
     *
     * @return string[]
     */
    public function getSpacesAsChoices(): array
    {
        $spaces = [];

        foreach ($this->clientsConfig as $clientConfig) {
            /** @var \Contentful\Delivery\Client $client */
            $client = $clientConfig['service'];

            $spaces[$client->getSpace()->getName()] = $clientConfig['space'];
        }

        return $spaces;
    }

    /**
     * Returns the list of spaces and content types for usage in Symfony Forms.
     */
    public function getSpacesAndContentTypesAsChoices(): array
    {
        $spaces = [];

        foreach ($this->clientsConfig as $clientConfig) {
            /** @var \Contentful\Delivery\Client $client */
            $client = $clientConfig['service'];

            $contentTypes = [];
            foreach ($client->getContentTypes()->getItems() as $contentType) {
                $contentTypes[$contentType->getName()] = $contentType->getId();
            }
            $spaces[$client->getSpace()->getName()] = $contentTypes;
        }

        return $spaces;
    }

    /**
     * Refreshes the Contentful entry for provided remote entry.
     */
    public function refreshContentfulEntry(DynamicEntry $remoteEntry): ?ContentfulEntry
    {
        $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);

        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setJson((string) json_encode($remoteEntry));
            $contentfulEntry->setIsPublished(true);
            $this->entityManager->persist($contentfulEntry);
            $this->entityManager->flush();
        } else {
            $contentfulEntry = $this->buildContentfulEntry($remoteEntry, $id);
        }

        return $contentfulEntry;
    }

    /**
     * Unpublishes the Contentful entry for provided remote entry.
     */
    public function unpublishContentfulEntry(DeletedEntry $remoteEntry): void
    {
        $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsPublished(false);
            $this->entityManager->persist($contentfulEntry);
            $this->entityManager->flush();
        }
    }

    /**
     * Deletes the Contentful entry for provided remote entry.
     */
    public function deleteContentfulEntry(DeletedEntry $remoteEntry): void
    {
        $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
        $contentfulEntry = $this->findContentfulEntry($id);
        if ($contentfulEntry instanceof ContentfulEntry) {
            $contentfulEntry->setIsDeleted(true);
            $this->entityManager->persist($contentfulEntry);

            foreach ($contentfulEntry->getRoutes() as $route) {
                $this->entityManager->remove($route);
            }

            $this->entityManager->flush();
        }
    }

    /**
     * Refreshes space caches for provided client.
     */
    public function refreshSpaceCache(Client $client): void
    {
        $spacePath = $this->getSpaceCachePath($client);
        $this->fileSystem->dumpFile($spacePath . '/space.json', (string) json_encode($client->getSpace()));
    }

    /**
     * Refreshes content type caches for provided client.
     */
    public function refreshContentTypeCache(Client $client): void
    {
        $spacePath = $this->getSpaceCachePath($client);
        $contentTypes = $client->getContentTypes();
        foreach ($contentTypes as $contentType) {
            $this->fileSystem->dumpFile($spacePath . '/ct-' . $contentType->getId() . '.json', (string) json_encode($contentType));
        }
    }

    /**
     * Returns the cache path for provided client.
     */
    public function getSpaceCachePath(Client $client): string
    {
        $space = $client->getSpace();
        $spacePath = $this->cacheDir . $space->getId();
        if (!$this->fileSystem->exists($spacePath)) {
            $this->fileSystem->mkdir($spacePath);
        }

        return $spacePath;
    }

    /**
     * Returns the Contentful entry with provided ID from the repository.
     *
     * Returns null if entry could not be found.
     */
    private function findContentfulEntry(string $id): ?ContentfulEntry
    {
        return $this->entityManager->getRepository(ContentfulEntry::class)->find($id);
    }

    /**
     * Builds the Contentful entry from provided remote entry.
     */
    private function buildContentfulEntry(EntryInterface $remoteEntry, string $id): ContentfulEntry
    {
        $contentfulEntry = new ContentfulEntry($remoteEntry);
        $contentfulEntry->setIsPublished(true);
        $contentfulEntry->setJson((string) json_encode($remoteEntry));

        $route = new Route();
        $route->setName($id);
        $route->setStaticPrefix($this->entrySlugger->getSlug($contentfulEntry));
        $route->setDefault(RouteObjectInterface::CONTENT_ID, ContentfulEntry::class . ':' . $id);
        $route->setContent($contentfulEntry);
        $contentfulEntry->addRoute($route); // Create the back-link from content to route

        $this->entityManager->persist($contentfulEntry);
        $this->entityManager->persist($route);
        $this->entityManager->flush();

        return $contentfulEntry;
    }

    /**
     * Builds the Contentful entries from provided remote entries.
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry[]
     */
    private function buildContentfulEntries(ResourceArray $entries, Client $client): array
    {
        $contentfulEntries = [];

        foreach ($entries as $remoteEntry) {
            $id = $remoteEntry->getSpace()->getId() . '|' . $remoteEntry->getId();
            $contentfulEntry = $this->findContentfulEntry($id);
            if (!$contentfulEntry instanceof ContentfulEntry) {
                $contentfulEntry = $this->buildContentfulEntry($remoteEntry, $id);
            } else {
                $contentfulEntry->reviveRemoteEntry($client);
            }
            $contentfulEntries[] = $contentfulEntry;
        }

        return $contentfulEntries;
    }
}
