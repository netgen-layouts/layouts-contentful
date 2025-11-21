<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Runtime;

use Netgen\Layouts\Contentful\Service\Contentful;
use Throwable;

final class ContentfulRuntime
{
    public function __construct(
        private Contentful $contentful,
    ) {}

    /**
     * Returns the Contentful entry name.
     */
    public function contentfulEntryName(string $entryId): string
    {
        try {
            $contentfulEntry = $this->contentful->loadContentfulEntry($entryId);
        } catch (Throwable) {
            return '';
        }

        return $contentfulEntry->getName();
    }

    /**
     * Returns the Contentful space name.
     */
    public function contentfulSpaceName(string $spaceId): string
    {
        return $this->contentful->getClientBySpaceId($spaceId)?->getSpace()->getName() ?? '';
    }

    /**
     * Returns the Contentful content type name.
     */
    public function contentfulContentTypeName(string $contentTypeId): string
    {
        return $this->contentful->getContentType($contentTypeId)?->getName() ?? '';
    }
}
