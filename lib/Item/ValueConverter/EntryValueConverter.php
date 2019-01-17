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

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     *
     * @return int|string
     */
    public function getId($object)
    {
        return $object->getId();
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     *
     * @return int|string
     */
    public function getRemoteId($object)
    {
        return $object->getId();
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     */
    public function getName($object): string
    {
        return $object->getName();
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     */
    public function getIsVisible($object): bool
    {
        return $object->getIsPublished();
    }

    public function getObject($object)
    {
        return $object;
    }
}
