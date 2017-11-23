<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry\ColumnValueProvider;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\BlockManager\Contentful\Browser\Item\Entry\Item;

final class ContentType implements ColumnValueProviderInterface
{
    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof Item) {
            return null;
        }

        return $item->getEntry()->getContentType()->getName();
    }
}
