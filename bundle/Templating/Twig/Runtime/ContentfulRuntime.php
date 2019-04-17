<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\Templating\Twig\Runtime;

use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Resource\ContentType;
use Netgen\Layouts\Contentful\Service\Contentful;
use Throwable;

final class ContentfulRuntime
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    /**
     * Returns the Contentful entry name.
     */
    public function contentfulEntryName(string $entryId): string
    {
        try {
            $contentfulEntry = $this->contentful->loadContentfulEntry($entryId);
        } catch (Throwable $t) {
            return '';
        }

        return $contentfulEntry->getName();
    }

    /**
     * Returns the Contentful space name.
     */
    public function contentfulSpaceName(string $spaceId): string
    {
        $client = $this->contentful->getClientBySpaceId($spaceId);
        if (!$client instanceof ClientInterface) {
            return '';
        }

        return $client->getSpace()->getName();
    }

    /**
     * Returns the Contentful content type name.
     */
    public function contentfulContentTypeName(string $contentTypeId): string
    {
        $contentType = $this->contentful->getContentType($contentTypeId);
        if (!$contentType instanceof ContentType) {
            return '';
        }

        return $contentType->getName();
    }
}
