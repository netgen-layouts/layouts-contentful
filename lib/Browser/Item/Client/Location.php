<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Location implements LocationInterface, ClientInterface
{
    public function __construct(private ContentfulClientInterface $client, private string $id) {}

    public function getLocationId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->client->getSpace()->getName();
    }

    public function getParentId(): ?string
    {
        return null;
    }

    public function getClient(): ContentfulClientInterface
    {
        return $this->client;
    }
}
