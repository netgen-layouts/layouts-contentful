<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\View\Matcher\Item;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\View\Matcher\MatcherInterface;
use Netgen\Layouts\View\View\ItemViewInterface;
use Netgen\Layouts\View\ViewInterface;
use function in_array;

final class Space implements MatcherInterface
{
    public function match(ViewInterface $view, array $config): bool
    {
        if (!$view instanceof ItemViewInterface) {
            return false;
        }

        $entry = $view->getItem()->getObject();
        if (!$entry instanceof ContentfulEntry) {
            return false;
        }

        return in_array($entry->getSpace()->getId(), $config, true);
    }
}
