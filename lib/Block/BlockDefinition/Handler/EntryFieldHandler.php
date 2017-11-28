<?php

namespace Netgen\BlockManager\Contentful\Block\BlockDefinition\Handler;

use Netgen\BlockManager\API\Values\Block\Block;
use Netgen\BlockManager\Block\BlockDefinition\BlockDefinitionHandler;
use Netgen\BlockManager\Block\DynamicParameters;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;
use Netgen\BlockManager\Parameters\ParameterType;

class EntryFieldHandler extends BlockDefinitionHandler
{
    /**
     * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(
        \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $contentful,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack
    ) {
        $this->contentful = $contentful;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function buildParameters(ParameterBuilderInterface $builder)
    {
        $builder->add('field_identifier', ParameterType\IdentifierType::class);
    }

    public function getDynamicParameters(DynamicParameters $params, Block $block)
    {
        $contentfulEntry = $this->request->attributes->get("contentDocument");
        $params['content'] = $contentfulEntry;

        try {
            $field = call_user_func(array($contentfulEntry, "get" . $block->getParameter("field_identifier") ));
        } catch (\Exception $e) {}

        $fieldType = $this->getFieldType($field);

        if ($fieldType == "dynamicentry") {
            $params['field_value'] = $this->contentful->loadContentfulEntry($field->getSpace()->getId() . "|" . $field->getId());
            $params['field_type'] = "entry";

        } elseif ($fieldType == "array") {
            $fieldValues = array();

            foreach ($field as $f) {
                $ft = $this->getFieldType($f);
                if ($ft == "dynamicentry") {
                    $fieldValues["entry"] = $this->contentful->loadContentfulEntry($f->getSpace()->getId() . "|" . $f->getId());
                } else {
                    $fieldValues[$ft] = $f;
                }
            }
            $params['field_value'] = $fieldValues;
            $params['field_type'] = $fieldType;

        } elseif ($fieldType != null) {
            $params['field_value'] = $field;
            $params['field_type'] = $fieldType;
        }

    }

    public function isContextual(Block $block)
    {
        return true;
    }

    private function getFieldType($field) {
        if ($field == null)
            return null;

        $fieldType = gettype($field);
        if ($fieldType == "object") {
            $classNameArray = explode("\\",get_class($field));
            $fieldType = strtolower(end($classNameArray));
        }

        return $fieldType;
    }
}
