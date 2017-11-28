<?php

namespace Netgen\BlockManager\Contentful\Layout\Resolver\ConditionType\FormMapper;

use Netgen\BlockManager\Layout\Resolver\Form\ConditionType\Mapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContentType extends Mapper
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful
     */
    private $contentful;

    public function __construct(
        \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful
    ) {
        $this->contentful = $contentful;
    }

    /**
     * Returns the form type that will be used to edit the value of this condition type.
     *
     * @return string
     */
    public function getFormType()
    {
        return ChoiceType::class;
    }

    public function getFormOptions()
    {
        return array(
            'choices' => $this->contentful->getSpacesAndContentTypesAsChoices(),
            'multiple' => true
        );
    }


}
