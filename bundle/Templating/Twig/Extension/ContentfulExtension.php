<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Templating\Twig\Extension;

use Exception;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentfulExtension extends AbstractExtension
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('contentful_entry_name', array($this, 'contentfulEntryName')),
            new TwigFunction('contentful_space_name', array($this, 'contentfulSpaceName')),
            new TwigFunction('contentful_content_type_name', array($this, 'contentfulContentTypeName')),
        );
    }

    public function contentfulEntryName($value)
    {
        try {
            $contentfulEntry = $this->contentful->loadContentfulEntry($value);
        } catch (Exception $e) {
            return '';
        }

        return $contentfulEntry->getName();
    }

    public function contentfulSpaceName($value)
    {
        $client = $this->contentful->getClientBySpaceId($value);

        return $client->getSpace()->getName();
    }

    public function contentfulContentTypeName($value)
    {
        $contentType = $this->contentful->getContentType($value);

        return $contentType->getName();
    }
}
