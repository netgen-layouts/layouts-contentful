<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\ColumnProvider\Entry;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Contentful\Browser\Item\Entry\EntryInterface;

final class ContentType implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EntryInterface) {
            return null;
        }

        $contentType = $item->getEntry()->getContentType();
        if ($contentType === null) {
            return null;
        }

        return $contentType->getName();
    }
}
