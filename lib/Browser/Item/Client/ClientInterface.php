<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client;

interface ClientInterface
{
    /**
     * Returns the Contentful client.
     */
    public function getClient(): ?Client;
}
