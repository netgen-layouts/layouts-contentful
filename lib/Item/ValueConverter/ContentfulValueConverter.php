<?php

namespace Netgen\BlockManager\Contentful\Item\ValueConverter;

use Netgen\ContentfulBlockManagerBundle\Entity\ContentfulEntry;
use Netgen\BlockManager\Item\ValueConverterInterface;

final class ContentfulValueConverter implements ValueConverterInterface
{
    public function supports($object)
    {
        return $object instanceof ContentfulEntry;
    }

    public function getValueType($object)
    {
        return 'contentful_entry';
    }

    public function getId($object)
    {
        return $object->getId();
    }

    public function getName($object)
    {
        return $object->getName();
    }

    public function getIsVisible($object)
    {
        return true;
    }
}
