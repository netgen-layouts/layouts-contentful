<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing\EntrySlugger;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Routing\EntrySluggerInterface;

final class Simple extends Slugger implements EntrySluggerInterface
{
    public function getSlug(ContentfulEntry $contentfulEntry): string
    {
        return '/' . $this->filterSlug($contentfulEntry->getName());
    }
}
