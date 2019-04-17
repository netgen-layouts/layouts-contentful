<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;

interface ClientInterface
{
    /**
     * Returns the Contentful client.
     */
    public function getClient(): ?ContentfulClientInterface;
}
