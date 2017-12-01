<?php

namespace Netgen\BlockManager\Contentful\Routing\EntrySlugger;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface;


class EntrySluggerConfigurable implements EntrySluggerInterface
{
    private $configuration;

    private $sluggers;

    public function __construct($configuration, $sluggers)
    {
        $this->configuration = $configuration;
        $this->sluggers = $sluggers;
    }

    public function getSlug(ContentfulEntry $contentfulEntry)
    {
        $slugger_type = $this->configuration['default'];

        $content_type_config = $this->configuration['content_type'];
        foreach ($content_type_config as $content_type_id => $content_type_slugger) {
            if ($contentfulEntry->getContentType()->getId() == $content_type_id) {
                $slugger_type = $content_type_slugger;
            }
        }

        return $this->sluggers[$slugger_type]->getSlug($contentfulEntry);
    }
}
