<?php

namespace Netgen\BlockManager\Contentful\Item\ValueLoader;

use Netgen\BlockManager\Contentful\Exception\NotFoundException;
use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\BlockManager\Exception\Item\ItemException;
use Netgen\BlockManager\Item\ValueLoaderInterface;

final class ContentfulValueLoader implements ValueLoaderInterface
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function load($id)
    {
        try {
            $contentfulEntry = $this->contentful->loadContentfulEntry($id);
        } catch (NotFoundException $e) {
            throw new ItemException(
                sprintf('Entry with ID "%s" could not be loaded.', $id),
                0,
                $e
            );
        }

        return $contentfulEntry;
    }
}
