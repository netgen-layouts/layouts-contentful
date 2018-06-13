<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Routing\EntrySlugger;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface;

final class Simple extends Slugger implements EntrySluggerInterface
{
    public function getSlug(ContentfulEntry $contentfulEntry): string
    {
        return '/' . $this->filterSlug($contentfulEntry->getName());
    }
}
