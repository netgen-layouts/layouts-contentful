<?php

namespace Netgen\BlockManager\Contentful\Browser\Backend;

use Contentful\Delivery\Client;
use Netgen\BlockManager\Contentful\Browser\Item\Client\Location;
use Netgen\BlockManager\Contentful\Browser\Item\Client\RootLocation;
use Netgen\BlockManager\Contentful\Browser\Item\Entry\Item;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry;
use Netgen\ContentBrowser\Backend\BackendInterface;
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

    public function getDefaultSections()
    {
        return array(new RootLocation());
    }

    public function loadLocation($id)
    {
        if ($id === '0') {
            return new RootLocation();
        }

        /** @var \Contentful\Delivery\Client $clientService */
        $clientService = $this->contentful->getClientByName($id);
        $space = $this->contentful->getSpaceByClientName($id);

        return new Location($clientService, $space);
    }

    /**
     * Loads a Contentful entry by its ID.
     *
     * @param string $id
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Entry\Item
     */
    public function loadItem($id)
    {
        /**
         * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry
         */
        $contentfulEntry = $this->contentful->loadContentfulEntry($id);

        return $this->buildItem($contentfulEntry);
    }

    public function getSubLocations(LocationInterface $location)
    {
        if (!$location instanceof RootLocation) {
            return array();
        }

        return $this->buildLocations(
            $this->contentful->getClients()
        );
    }

    public function getSubLocationsCount(LocationInterface $location)
    {
        if (!$location instanceof RootLocation) {
            return 0;
        }

        return count($this->contentful->getClients());
    }

    /**
     * Loads Contentful entries located below provided location.
     *
     * @param \Netgen\ContentBrowser\Item\LocationInterface $location
     * @param int $offset
     * @param int $limit
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Entry\Item[]
     */
    public function getSubItems(LocationInterface $location, $offset = 0, $limit = 25)
    {
        if ($location instanceof RootLocation) {
            $contentfulEntries = $this->contentful->getContentfulEntries($offset, $limit);
        } else {
            $contentfulEntries = $this->contentful->getContentfulEntries($offset, $limit, $location->getClient());
        }

        return $this->buildItems($contentfulEntries);
    }

    public function getSubItemsCount(LocationInterface $location)
    {
        if ($location instanceof RootLocation) {
            return array();
        }

        return $this->contentful->getContentfulEntriesCount($location->getClient());
    }

    public function search($searchText, $offset = 0, $limit = 25)
    {
        return $this->buildItems(
            $this->contentful->searchContentfulEntries($searchText, $offset, $limit)
        );
    }

    public function searchCount($searchText)
    {
        return $this->contentful->searchContentfulEntriesCount($searchText);
    }

    /**
     * Builds the location from provided client.
     *
     * @param \Contentful\Delivery\Client $client
     * @param string $id
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Client\Location
     */
    private function buildLocation(Client $client, $id)
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
    private function buildLocations($clients)
    {
        return array_map(
            function (Client $client, $id) {
                return $this->buildLocation($client, $id);
            },
            $clients,
            $this->contentful->getClientsNames()
        );
    }

    /**
     * Builds the item from provided client.
     *
     * @param \Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry $entry
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Entry\Item
     */
    private function buildItem(ContentfulEntry $entry)
    {
        return new Item($entry);
    }

    /**
     * Builds the locations from provided clients.
     *
     * @param \Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry[] $entries
     *
     * @return \Netgen\BlockManager\Contentful\Browser\Item\Entry\Item[]
     */
    private function buildItems($entries)
    {
        return array_map(
            function (ContentfulEntry $entry) {
                return $this->buildItem($entry);
            },
            $entries
        );
    }
}
