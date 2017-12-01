<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Client;

interface ClientInterface
{
    /**
     * Returns the Contentful client.
     *
     * @return \Contentful\Delivery\Client
     */
    public function getClient();
}