<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Backend;

use Contentful\Delivery\Client;
use Netgen\BlockManager\Contentful\Browser\Item\Client\ClientInterface;
use Netgen\BlockManager\Contentful\Browser\Item\Client\Location;
use Netgen\BlockManager\Contentful\Browser\Item\Client\RootLocation;
use Netgen\BlockManager\Contentful\Browser\Item\Entry\Item;
use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class ContentfulBackend implements BackendInterface
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getDefaultSections(): array
    {
        return [new RootLocation()];
    }

    public function loadLocation($id): LocationInterface
    {
        if ($id === '0') {
            return new RootLocation();
        }

        $clientService = $this->contentful->getClientByName($id);
        $space = $this->contentful->getSpaceByClientName($id);

        return new Location($clientService, $space->getId());
    }

    public function loadItem($id): ItemInterface
    {
        $contentfulEntry = $this->contentful->loadContentfulEntry($id);

        return $this->buildItem($contentfulEntry);
    }

    public function getSubLocations(LocationInterface $location)
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

    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        $contentfulEntries = [];

        if ($location instanceof RootLocation) {
            $contentfulEntries = $this->contentful->getContentfulEntries($offset, $limit);
        } elseif ($location instanceof Location) {
            $contentfulEntries = $this->contentful->getContentfulEntries($offset, $limit, $location->getClient());
        }

        return $this->buildItems($contentfulEntries);
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof ClientInterface || $location instanceof RootLocation) {
            return 0;
        }

        return $this->contentful->getContentfulEntriesCount($location->getClient());
    }

    public function search($searchText, $offset = 0, $limit = 25)
    {
        return $this->buildItems(
            $this->contentful->searchContentfulEntries($searchText, $offset, $limit)
        );
    }

    public function searchCount($searchText): int
    {
        return $this->contentful->searchContentfulEntriesCount($searchText);
    }

    /**
     * Builds the location from provided client.
     */
    private function buildLocation(Client $client, string $id): Location
    {
        return new Location($client, $id);
    }

    /**
     * Builds the locations from provided clients.
     *
     * @param \Contentful\Delivery\Client[] $clients
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Client\Location[]
     */
    private function buildLocations(array $clients): array
    {
        return array_map(
            function (Client $client, string $id): Location {
                return $this->buildLocation($client, $id);
            },
            $clients,
            $this->contentful->getClientsNames()
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
     * @param \Netgen\BlockManager\Contentful\Entity\ContentfulEntry[] $entries
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Entry\Item[]
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
