<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Item\ValueConverter;

use Netgen\BlockManager\Item\ValueConverterInterface;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;

final class EntryValueConverter implements ValueConverterInterface
{
    public function supports($object): bool
    {
        return $object instanceof ContentfulEntry;
    }

    public function getValueType($object): string
    {
        return 'contentful_entry';
    }

    public function getId($object)
    {
        return $object->getId();
    }

    public function getRemoteId($object)
    {
        return $object->getId();
    }

    public function getName($object): string
    {
        return $object->getName();
    }

    public function getIsVisible($object): bool
    {
        return $object->getIsPublished();
    }

    public function getObject($object)
    {
        return $object;
    }
}
