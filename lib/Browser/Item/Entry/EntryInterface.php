<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;

interface EntryInterface
{
    /**
     * Returns the Contentful entry.
     */
    public function getEntry(): ContentfulEntry;
}
