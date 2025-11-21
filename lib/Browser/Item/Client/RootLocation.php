<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Client;

use Netgen\ContentBrowser\Item\LocationInterface;

final class RootLocation implements LocationInterface, ClientInterface
{
    public string $locationId {
        get => '0';
    }

    public string $name {
        get => 'Content';
    }

    public null $parentId {
        get => null;
    }

    public null $client {
        get => null;
    }
}
