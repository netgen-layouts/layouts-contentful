<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Backend;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\Contentful\Browser\Item\Client\ClientInterface;
use Netgen\Layouts\Contentful\Browser\Item\Client\Location;
use Netgen\Layouts\Contentful\Browser\Item\Client\RootLocation;
use Netgen\Layouts\Contentful\Browser\Item\Entry\Item;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;

final class ContentfulBackend implements BackendInterface
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getSections(): iterable
    {
        return [new RootLocation()];
    }

    public function loadLocation($id): LocationInterface
    {
        if ($id === '0') {
            return new RootLocation();
        }

        $clientService = $this->contentful->getClientByName((string) $id);
        $space = $this->contentful->getSpaceByClientName((string) $id);

        return new Location($clientService, $space);
    }

    public function loadItem($value): ItemInterface
    {
        $contentfulEntry = $this->contentful->loadContentfulEntry((string) $value);

        return $this->buildItem($contentfulEntry);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        if (!$location instanceof RootLocation) {
            return [];
        }

        return $this->buildLocations(
            $this->contentful->getClients()
        );
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        if (!$location instanceof RootLocation) {
            return 0;
        }

        return count($this->contentful->getClients());
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        if (!$location instanceof ClientInterface) {
            return [];
        }

        return $this->buildItems(
            $this->contentful->getContentfulEntries(
                $offset,
                $limit,
                $location->getClient()
            )
        );
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof ClientInterface || !$location->getClient() instanceof ContentfulClientInterface) {
            return 0;
        }

        return $this->contentful->getContentfulEntriesCount($location->getClient());
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        return $this->buildItems(
            $this->contentful->searchContentfulEntries($searchText, $offset, $limit)
        );
    }

    public function searchCount(string $searchText): int
    {
        return $this->contentful->searchContentfulEntriesCount($searchText);
    }

    /**
     * Builds the location from provided client.
     */
    private function buildLocation(ContentfulClientInterface $client, string $id): Location
    {
        return new Location($client, $id);
    }

    /**
     * Builds the locations from provided clients.
     *
     * @param \Contentful\Delivery\Client\ClientInterface[] $clients
     *
     * @return \Netgen\Layouts\Contentful\Browser\Item\Client\Location[]
     */
    private function buildLocations(array $clients): array
    {
        return array_map(
            function (ContentfulClientInterface $client, string $id): Location {
                return $this->buildLocation($client, $id);
            },
            $clients,
            array_keys($clients)
        );
    }

    /**
     * Builds the item from provided client.
     */
    private function buildItem(ContentfulEntry $entry): Item
    {
        return new Item($entry);
    }

    /**
     * Builds the locations from provided clients.
     *
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry[] $entries
     *
     * @return \Netgen\Layouts\Contentful\Browser\Item\Entry\Item[]
     */
    private function buildItems(array $entries): array
    {
        return array_map(
            function (ContentfulEntry $entry): Item {
                return $this->buildItem($entry);
            },
            $entries
        );
    }
}
