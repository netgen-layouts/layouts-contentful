<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry;

interface EntryInterface
{
    /**
     * Returns the Contentful entry.
     *
     * @return \Netgen\BlockManager\Contentful\Entity\ContentfulEntry
     */
    public function getEntry();
}
