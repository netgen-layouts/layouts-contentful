<?php

declare(strict_types=1);

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Runtime;

use Contentful\Delivery\Client;
use Contentful\Delivery\ContentType;
use Exception;
use Netgen\BlockManager\Contentful\Service\Contentful;

final class ContentfulRuntime
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
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
        } catch (Exception $e) {
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
        if (!$client instanceof Client) {
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
