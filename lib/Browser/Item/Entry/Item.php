<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Entry;

use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;

final class Item implements ItemInterface, EntryInterface
{
    private ContentfulEntry $entry;

    public function __construct(ContentfulEntry $entry)
    {
        $this->entry = $entry;
    }

    public function getValue(): string
    {
        return $this->entry->getId();
    }

    public function getName(): string
    {
        return $this->getEntry()->getName();
    }

    public function isVisible(): bool
    {
        return true;
    }

    public function isSelectable(): bool
    {
        return true;
    }

    public function getEntry(): ContentfulEntry
    {
        return $this->entry;
    }
}
