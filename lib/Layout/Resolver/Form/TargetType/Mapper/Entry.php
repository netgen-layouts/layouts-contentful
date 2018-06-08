<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\BlockManager\Layout\Resolver\Form\TargetType\Mapper;
use Netgen\ContentBrowser\Form\Type\ContentBrowserType;

final class Entry extends Mapper
{
    public function getFormType()
    {
        return ContentBrowserType::class;
    }

    public function getFormOptions()
    {
        return [
            'item_type' => 'contentful_entry',
        ];
    }
}
