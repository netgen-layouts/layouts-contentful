<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Routing\EntrySlugger;

use Netgen\BlockManager\Contentful\Entity\ContentfulEntry;
use Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface;

final class Configurable implements EntrySluggerInterface
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @var \Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface[]
     */
    private $sluggers;

    /**
     * @param array $configuration
     * @param \Netgen\BlockManager\Contentful\Routing\EntrySluggerInterface[] $sluggers
     */
    public function __construct(array $configuration, array $sluggers)
    {
        $this->configuration = $configuration;
        $this->sluggers = $sluggers;
    }

    public function getSlug(ContentfulEntry $contentfulEntry)
    {
        $sluggerType = $this->configuration['default'];

        $contentTypeConfig = $this->configuration['content_type'];
        foreach ($contentTypeConfig as $contentTypeId => $contentTypeSlugger) {
            if ($contentfulEntry->getContentType()->getId() === $contentTypeId) {
                $sluggerType = $contentTypeSlugger;
            }
        }

        return $this->sluggers[$sluggerType]->getSlug($contentfulEntry);
    }
}
