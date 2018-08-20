<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Browser\Item\ColumnProvider\Entry;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Contentful\Browser\Item\Entry\EntryInterface;

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

        $createdAt = $item->getEntry()->getCreatedAt();
        if ($createdAt === null) {
            return null;
        }

        return $createdAt->format($this->dateFormat);
    }
}
