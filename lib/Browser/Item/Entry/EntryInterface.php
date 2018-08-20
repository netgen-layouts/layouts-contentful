<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\Entry;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;

interface EntryInterface
{
    /**
     * Returns the Contentful entry.
     */
    public function getEntry(): ContentfulEntry;
}
