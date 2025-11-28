<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Entry;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;

final class Item implements ItemInterface, EntryInterface
{
    public string $value {
        get => $this->entry->getId();
    }

    public string $name {
        get => $this->entry->getName();
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
