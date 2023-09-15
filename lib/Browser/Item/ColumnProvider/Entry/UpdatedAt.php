<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\ColumnProvider\Entry;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Contentful\Browser\Item\Entry\EntryInterface;

final class UpdatedAt implements ColumnValueProviderInterface
{
    public function __construct(private string $dateFormat) {}

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof EntryInterface) {
            return null;
        }

        return $item->getEntry()->getUpdatedAt()->format($this->dateFormat);
    }
}
