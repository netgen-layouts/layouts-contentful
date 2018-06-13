<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Browser\Item\ColumnProvider\Entry;

use Netgen\BlockManager\Contentful\Browser\Item\Entry\EntryInterface;
use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;

final class CreatedAt implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    private $dateFormat;

    public function __construct(string $dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EntryInterface) {
            return null;
        }

        return $item->getEntry()->getCreatedAt()->format($this->dateFormat);
    }
}
