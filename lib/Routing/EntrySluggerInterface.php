<?php

namespace Netgen\BlockManager\Contentful\Routing;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;

interface EntrySluggerInterface
{
    /**
     * Returns the slug for the provided entry.
     *
     * @param \Netgen\BlockManager\Contentful\Entity\ContentfulEntry $contentfulEntry
     *
     * @return string
     */
    public function getSlug(ContentfulEntry $contentfulEntry);
}
