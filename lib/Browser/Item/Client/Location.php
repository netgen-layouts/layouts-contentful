<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Location implements LocationInterface, ClientInterface
{
    /**
     * @var \Contentful\Delivery\Client
     */
    private $client;

    /**
     * @var string
     */
    private $id;

    public function __construct(Client $client, string $id)
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
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
