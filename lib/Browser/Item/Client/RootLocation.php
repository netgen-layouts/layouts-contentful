<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Item\Client;

use Netgen\ContentBrowser\Item\LocationInterface;

final class RootLocation implements LocationInterface, ClientInterface
{
    public function getLocationId()
    {
        return 0;
    }

    public function getName()
    {
        return 'Content';
    }

    public function getParentId()
    {
    }

    public function getClient()
    {
    }
}
