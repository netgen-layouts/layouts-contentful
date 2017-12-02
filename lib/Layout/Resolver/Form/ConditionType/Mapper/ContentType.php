<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\Form\ConditionType\Mapper;

use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\BlockManager\Layout\Resolver\Form\ConditionType\Mapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class ContentType extends Mapper
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getFormType()
    {
        return ChoiceType::class;
    }

    public function getFormOptions()
    {
        return array(
            'choices' => $this->contentful->getSpacesAndContentTypesAsChoices(),
            'multiple' => true,
        );
    }
}
