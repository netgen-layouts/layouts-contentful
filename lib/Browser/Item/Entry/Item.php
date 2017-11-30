<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry;

use Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry;
use Netgen\ContentBrowser\Item\ItemInterface;

final class Item implements ItemInterface, EntryInterface
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry
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

    public function getEntry()
    {
        return $this->entry;
    }
}
