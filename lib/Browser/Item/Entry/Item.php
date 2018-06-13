<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Item implements ItemInterface, EntryInterface
{
    /**
     * @var \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    private $entry;

    public function __construct(ContentfulEntry $entry)
    {
        $this->entry = $entry;
    }

    public function getValue()
    {
        return $this->entry->getId();
    }

    public function getName()
    {
        return $this->getEntry()->getName();
    }

    public function isVisible()
    {
        return true;
    }

    public function isSelectable()
    {
        return true;
    }

    public function getEntry(): ContentfulEntry
    {
        return $this->entry;
    }
}
