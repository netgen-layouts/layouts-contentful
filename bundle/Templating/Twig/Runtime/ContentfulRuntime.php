<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Runtime;

use Netgen\Layouts\Contentful\Service\Contentful;

final class ContentfulRuntime
{
    public function __construct(
        private Contentful $contentful,
    ) {}

    /**
     * Returns the Contentful content type name.
     */
    public function contentfulContentTypeName(string $contentTypeId): string
    {
        return $this->contentful->getContentType($contentTypeId)?->getName() ?? '';
    }
}
