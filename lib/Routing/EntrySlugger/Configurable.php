<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing\EntrySlugger;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Routing\EntrySluggerInterface;

final class Configurable implements EntrySluggerInterface
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @var \Netgen\Layouts\Contentful\Routing\EntrySluggerInterface[]
     */
    private $sluggers;

    /**
     * @param array<string, mixed> $configuration
     * @param \Netgen\Layouts\Contentful\Routing\EntrySluggerInterface[] $sluggers
     */
    public function __construct(array $configuration, array $sluggers)
    {
        $this->configuration = $configuration;
        $this->sluggers = $sluggers;
    }

    public function getSlug(ContentfulEntry $contentfulEntry): string
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
