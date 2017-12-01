<?php

namespace Netgen\BlockManager\Contentful\Routing\EntrySlugger;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Routing\BaseSlugger;
use Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface;


class EntrySluggerWithSpaceName extends BaseSlugger implements EntrySluggerInterface
{
    public function getSlug(ContentfulEntry $contentfulEntry)
    {
        $slug  = "/" . $this->createSlugPart($contentfulEntry->getSpace()->getName());
        $slug .= "/" . $this->createSlugPart($contentfulEntry->getName());
        
        return $slug;
    }
}
