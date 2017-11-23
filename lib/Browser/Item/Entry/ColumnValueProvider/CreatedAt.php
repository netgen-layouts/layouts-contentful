<?php

namespace Netgen\BlockManager\Contentful\Browser\Item\Entry\ColumnValueProvider;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\BlockManager\Contentful\Browser\Item\Entry\Item;

final class CreatedAt implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    private $dateFormat;

    /**
     * Constructor.
     *
     * @param string $dateFormat
     */
    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getValue(ItemInterface $item)
    {
        if (!$item instanceof Item) {
            return null;
        }

        return $item->getEntry()->getCreatedAt()->format(
            $this->dateFormat
        );
    }
}
