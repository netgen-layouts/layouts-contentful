<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Runtime;

use Exception;
use Netgen\BlockManager\Contentful\Service\Contentful;

class ContentfulRuntime
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
     * @param int|string $entryId
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
     * @param int|string $spaceId
     *
     * @return string
     */
    public function contentfulSpaceName($spaceId)
    {
        $client = $this->contentful->getClientBySpaceId($spaceId);

        return $client->getSpace()->getName();
    }

    /**
     * Returns the Contentful content type name.
     *
     * @param int|string $contentTypeId
     *
     * @return string
     */
    public function contentfulContentTypeName($contentTypeId)
    {
        $contentType = $this->contentful->getContentType($contentTypeId);

        return $contentType->getName();
    }
}
