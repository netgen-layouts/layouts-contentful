<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Item\ValueConverter;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Item\ValueConverterInterface;

/**
 * @implements \Netgen\Layouts\Item\ValueConverterInterface<\Netgen\Layouts\Contentful\Entity\ContentfulEntry>
 */
final class EntryValueConverter implements ValueConverterInterface
{
    public function supports(object $object): bool
    {
        return $object instanceof ContentfulEntry;
    }

    public function getValueType(object $object): string
    {
        return 'contentful_entry';
    }

    public function getId(object $object): string
    {
        return $object->id;
    }

    public function getRemoteId(object $object): string
    {
        return $object->id;
    }

    public function getName(object $object): string
    {
        return $object->name;
    }

    public function getIsVisible(object $object): bool
    {
        return $object->isPublished;
    }

    public function getObject(object $object): ContentfulEntry
    {
        return $object;
    }
}
