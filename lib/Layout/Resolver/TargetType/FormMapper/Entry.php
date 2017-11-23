<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\TargetType\FormMapper;

use Netgen\BlockManager\Layout\Resolver\Form\TargetType\Mapper;
use Netgen\ContentBrowser\Form\Type\ContentBrowserType;

class Entry extends Mapper
{
    /**
     * Returns the form type that will be used to edit the value of this condition type.
     *
     * @return string
     */
    public function getFormType()
    {
        return ContentBrowserType::class;
    }

    public function getFormOptions()
    {
        return array(
            'item_type' => 'contentful_entry',
        );
    }
}
