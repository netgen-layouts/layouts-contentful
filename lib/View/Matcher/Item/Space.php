<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\View\Matcher\Item;

use Netgen\BlockManager\View\Matcher\MatcherInterface;
use Netgen\BlockManager\View\View\ItemViewInterface;
use Netgen\BlockManager\View\ViewInterface;

final class Space implements MatcherInterface
{
    public function match(ViewInterface $view, array $config): bool
    {
        if (!$view instanceof ItemViewInterface) {
            return false;
        }

        /** @var \Netgen\Layouts\Contentful\Entity\ContentfulEntry|null $entry */
        $entry = $view->getItem()->getObject();

        if ($entry === null) {
            return false;
        }

        return in_array($entry->getSpace()->getId(), $config, true);
    }
}
