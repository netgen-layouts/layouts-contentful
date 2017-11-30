<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client;
use Netgen\ContentBrowser\Item\LocationInterface;

final class Location implements LocationInterface
{
    /**
     * @var \Contentful\Delivery\Client
     */
    private $client;

    private $id;

    public function __construct(Client $client, $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    public function getLocationId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->client->getSpace()->getName();
    }

    public function getParentId()
    {
        return null;
    }

    public function getClient()
    {
        return $this->client;
    }
}
