<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Client;

use Contentful\Delivery\Client\ClientInterface as ContentfulClientInterface;
use Netgen\ContentBrowser\Item\LocationInterface;

final class RootLocation implements LocationInterface, ClientInterface
{
    public function getLocationId()
    {
        return '0';
    }

    public function getName(): string
    {
        return 'Content';
    }

    public function getParentId()
    {
        return null;
    }

    public function getClient(): ?ContentfulClientInterface
    {
        return null;
    }
}
