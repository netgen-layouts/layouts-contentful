<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Entry;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;

final class Item implements ItemInterface, EntryInterface
{
    public string $value {
        get => $this->entry->id;
    }

    public string $name {
        get => $this->entry->name;
    }

    public true $isVisible {
        get => true;
    }

    public true $isSelectable {
        get => true;
    }

    public function __construct(
        public private(set) ContentfulEntry $entry,
    ) {}
}
