<?php

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
     *
     * @param string $entryId
     *
     * @return string
     */
    public function contentfulEntryName($entryId)
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
     *
     * @param string $spaceId
     *
     * @return string
     */
    public function contentfulSpaceName($spaceId)
    {
        $client = $this->contentful->getClientBySpaceId($spaceId);
        if (!$client instanceof Client) {
            return '';
        }

        return $client->getSpace()->getName();
    }

    /**
     * Returns the Contentful content type name.
     *
     * @param string $contentTypeId
     *
     * @return string
     */
    public function contentfulContentTypeName($contentTypeId)
    {
        $contentType = $this->contentful->getContentType($contentTypeId);
        if (!$contentType instanceof ContentType) {
            return '';
        }

        return $contentType->getName();
    }
}
