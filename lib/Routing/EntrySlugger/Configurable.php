<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Routing\EntrySlugger;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Exception\RuntimeException;
use Netgen\Layouts\Contentful\Routing\EntrySluggerInterface;
use Psr\Container\ContainerInterface;

final class Configurable implements EntrySluggerInterface
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $sluggers;

    public function __construct(array $configuration, ContainerInterface $sluggers)
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

        return $this->getSlugger($sluggerType)->getSlug($contentfulEntry);
    }

    /**
     * Returns the slugger with provided identifier from the collection.
     *
     * @throws \Netgen\Layouts\Contentful\Exception\RuntimeException If the slugger does not exist or is not of correct type
     */
    private function getSlugger(string $sluggerType): EntrySluggerInterface
    {
        if (!$this->sluggers->has($sluggerType)) {
            throw new RuntimeException(sprintf('Slugger with "%s" type does not exist.', $sluggerType));
        }

        $slugger = $this->sluggers->get($sluggerType);
        if (!$slugger instanceof EntrySluggerInterface) {
            throw new RuntimeException(sprintf('Slugger with "%s" type does not exist.', $sluggerType));
        }

        return $slugger;
    }
}
