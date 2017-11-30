<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry;

interface EntryInterface
{
    /**
     * Returns the Contentful entry.
     *
     * @return \Netgen\Bundle\ContentfulBlockManagerBundle\Entity\ContentfulEntry
     */
    public function getEntry();
}
