<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\View\Matcher\Item;

use Netgen\BlockManager\View\Matcher\MatcherInterface;
use Netgen\BlockManager\View\View\ItemViewInterface;
use Netgen\BlockManager\View\ViewInterface;

final class ContentType implements MatcherInterface
{
    public function match(ViewInterface $view, array $config)
    {
        if (!$view instanceof ItemViewInterface) {
            return false;
        }

        return in_array($view->getItem()->getObject()->getContentType()->getId(), $config, true);
    }
}
