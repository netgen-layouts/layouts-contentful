<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Location implements LocationInterface, ClientInterface
{
    public string $name {
        get => $this->client->getSpace()->getName();
    }

    public null $parentId {
        get => null;
    }

    public function __construct(
        private(set) ContentfulClientInterface $client,
        private(set) string $locationId,
    ) {}
}
