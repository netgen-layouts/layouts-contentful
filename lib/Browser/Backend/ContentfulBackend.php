<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Backend;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Backend\SearchQuery;
use Netgen\ContentBrowser\Backend\SearchResult;
use Netgen\ContentBrowser\Backend\SearchResultInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\Contentful\Browser\Item\Client\ClientInterface;
use Netgen\Layouts\Contentful\Browser\Item\Client\Location;
use Netgen\Layouts\Contentful\Browser\Item\Client\RootLocation;
use Netgen\Layouts\Contentful\Browser\Item\Entry\Item;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;

use function count;

final class ContentfulBackend implements BackendInterface
{
    public function __construct(
        private Contentful $contentful,
    ) {}

    public function getSections(): iterable
    {
        return [new RootLocation()];
    }

    public function loadLocation(int|string $id): LocationInterface
    {
        if ($id === '0') {
            return new RootLocation();
        }

        $clientService = $this->contentful->getClientByName((string) $id);

        return new Location($clientService, $clientService->getSpace()->getId());
    }

    public function loadItem(int|string $value): ItemInterface
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
            $this->contentful->getClients(),
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
                $location->client,
            ),
        );
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        if (!$location instanceof ClientInterface || !$location->client instanceof ContentfulClientInterface) {
            return 0;
        }

        return $this->contentful->getContentfulEntriesCount($location->client);
    }

    public function searchItems(SearchQuery $searchQuery): SearchResultInterface
    {
        return new SearchResult(
            $this->buildItems(
                $this->contentful->searchContentfulEntries(
                    $searchQuery->searchText,
                    $searchQuery->offset,
                    $searchQuery->limit,
                ),
            ),
        );
    }

    public function searchItemsCount(SearchQuery $searchQuery): int
    {
        return $this->contentful->searchContentfulEntriesCount($searchQuery->searchText);
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
     * @param iterable<\Contentful\Delivery\Client\ClientInterface> $clients
     *
     * @return iterable<\Netgen\Layouts\Contentful\Browser\Item\Client\Location>
     */
    private function buildLocations(iterable $clients): iterable
    {
        foreach ($clients as $id => $client) {
            yield $this->buildLocation($client, $id);
        }
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
     * @param iterable<\Netgen\Layouts\Contentful\Entity\ContentfulEntry> $entries
     *
     * @return iterable<\Netgen\Layouts\Contentful\Browser\Item\Entry\Item>
     */
    private function buildItems(iterable $entries): iterable
    {
        foreach ($entries as $entry) {
            yield $this->buildItem($entry);
        }
    }
}
