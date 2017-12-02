<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\ColumnProvider\Entry;

use Netgen\BlockManager\Contentful\Browser\Item\Entry\EntryInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class UpdatedAt implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @param string $dateFormat
     */
    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof EntryInterface) {
            return null;
        }

        return $item->getEntry()->getUpdatedAt()->format($this->dateFormat);
    }
}
