<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Routing;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;

interface EntrySluggerInterface
{
    /**
     * Returns the slug for the provided entry.
     */
    public function getSlug(ContentfulEntry $contentfulEntry): string;
}
