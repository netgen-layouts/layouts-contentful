<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Twig\Extensions;

use Twig_Extension;
use Twig_SimpleFunction;

class ContentfulExtension extends Twig_Extension
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful
     */
    private $contentful;

    public function __construct(
        \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful
    ) {
        $this->contentful = $contentful;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('contentful_entry_name', array($this, 'contentfulEntryName')),
            new Twig_SimpleFunction('contentful_space_name', array($this, 'contentfulSpaceName')),
            new Twig_SimpleFunction('contentful_content_type_name', array($this, 'contentfulContentTypeName'))
        );
    }

    public function contentfulEntryName($value)
    {
        return $this->contentful->loadContentfulEntry($value)->getName();
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
