<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Routing\EntrySlugger;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface;

final class WithSpace extends Slugger implements EntrySluggerInterface
{
    public function getSlug(ContentfulEntry $contentfulEntry)
    {
        return '/' . $this->filterSlug($contentfulEntry->getSpace()->getName()) .
            '/' . $this->filterSlug($contentfulEntry->getName());
    }
}
