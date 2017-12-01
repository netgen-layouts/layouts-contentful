<?php

namespace Netgen\BlockManager\Contentful\Routing;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;

interface EntrySluggerInterface
{
    /**
     * Returns the slug
     *
     * @return string
     */
    public function getSlug(ContentfulEntry $contentfulEntry);
}
