<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Location implements LocationInterface, ClientInterface
{
    private ContentfulClientInterface $client;

    private string $id;

    public function __construct(ContentfulClientInterface $client, string $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    public function getLocationId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->client->getSpace()->getName();
    }

    public function getParentId()
    {
        return null;
    }

    public function getClient(): ?ContentfulClientInterface
    {
        return $this->client;
    }
}
