<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;

interface EntrySluggerInterface
{
    /**
     * Returns the slug for the provided entry.
     */
    public function getSlug(ContentfulEntry $contentfulEntry): string;
}
