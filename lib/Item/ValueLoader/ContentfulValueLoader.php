<?php

namespace Netgen\BlockManager\Contentful\Item\ValueLoader;

use Netgen\BlockManager\Item\ValueLoaderInterface;

final class ContentfulValueLoader implements ValueLoaderInterface
{
    /**
     * @var \Netgen\ContentfulBlockManagerBundle\Service\Contentful
     */
    private $contentful;

    public function __construct(
        \Netgen\ContentfulBlockManagerBundle\Service\Contentful $contentful
    ) {
        $this->contentful = $contentful;
    }

    public function load($id)
    {
        return $this->contentful->loadContentfulEntry($id);
    }
}
