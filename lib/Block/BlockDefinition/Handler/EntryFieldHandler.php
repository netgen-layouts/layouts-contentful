<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use Contentful\Delivery\Resource\Asset;
use Contentful\RichText\Node\NodeInterface;
use Contentful\RichText\Parser;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandler;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Collection\Result\Result;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Item\CmsItemBuilderInterface;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

final class EntryFieldHandler extends BlockDefinitionHandler
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Contentful\RichText\Parser
     */
    private $richTextParser;

    /**
     * @var \Netgen\Layouts\Item\CmsItemBuilderInterface
     */
    private $cmsItemBuilder;

    public function __construct(Contentful $contentful, RequestStack $requestStack, Parser $parser, CmsItemBuilderInterface $cmsItemBuilder)
    {
        $this->contentful = $contentful;
        $this->requestStack = $requestStack;
        $this->richTextParser = $parser;
        $this->cmsItemBuilder = $cmsItemBuilder;
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add(
            'field_identifier',
            ParameterType\IdentifierType::class
        );

        $builder->add(
            'width',
            ParameterType\NumberType::class,
            [
                'required' => true,
                'default_value' => 0,
                'min' => 0,
                'max' => 4096,
            ]
        );

        $builder->add(
            'height',
            ParameterType\NumberType::class,
            [
                'required' => true,
                'default_value' => 0,
                'min' => 0,
                'max' => 4096,
            ]
        );

        $builder->add(
            'html_element',
            ParameterType\ChoiceType::class,
            [
                'options' => [
                    'Div' => 'div',
                    'Span' => 'span',
                    'Paragraph' => 'p',
                    'Heading 1' => 'h1',
                    'Heading 2' => 'h2',
                    'Heading 3' => 'h3',
                ],
                'multiple' => false,
                'label' => 'Use HTML element',
            ]
        );

        $builder->add(
            'date_format',
            ParameterType\TextLineType::class,
            [
                'required' => true,
                'default_value' => 'd/m/Y',
            ]
        );

        $builder->add(
            'zoom',
            ParameterType\RangeType::class,
            [
                'required' => true,
                'default_value' => 5,
                'min' => 0,
                'max' => 20,
            ]
        );

        $builder->add(
            'map_type',
            ParameterType\ChoiceType::class,
            [
                'required' => true,
                'options' => [
                    'ROADMAP' => 'block.map.map_type.roadmap',
                    'SATELLITE' => 'block.map.map_type.satellite',
                    'HYBRID' => 'block.map.map_type.hybrid',
                    'TERRAIN' => 'block.map.map_type.terrain', ],
            ]
        );

        $builder->add(
            'show_marker',
            ParameterType\BooleanType::class,
            [
                'default_value' => true,
            ]
        );
    }

    public function getDynamicParameters(DynamicParameters $params, Block $block): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return;
        }

        $contentfulEntry = $currentRequest->attributes->get('contentDocument');
        $params['content'] = $contentfulEntry;

        if (!$contentfulEntry->has($block->getParameter('field_identifier')->getValue())) {
            return;
        }
        $innerField = $contentfulEntry->get($block->getParameter('field_identifier')->getValue());

        $field = new ContentfulEntryField($innerField);

        if (!$field->isValueSet() && is_array($innerField)) {
            try {
                if (array_key_exists('content', $innerField) && array_key_exists('nodeType', $innerField)) {
                    $field->setValue($this->loadRichText($innerField), 'richtext');
                } elseif (array_key_exists('lon', $innerField) && array_key_exists('lat', $innerField)) {
                    $field->setValue($innerField, 'geolocation');
                } elseif (array_key_exists('sys', $innerField)) {
                    if ($innerField['sys']['linkType'] === 'Entry') {
                        $field->setValue($this->loadEntry($innerField, $contentfulEntry->getSpace()), 'entry');
                    }
                    if ($innerField['sys']['linkType'] === 'Asset') {
                        $field->setValue($this->loadAsset($innerField, $contentfulEntry->getSpace()), 'asset');
                    }
                } elseif (array_keys($innerField) === range(0, count($innerField) - 1)) {
                    $fieldValues = [];
                    $fieldType = 'entries';
                    foreach ($innerField as $inner) {
                        if ($inner['sys']['linkType'] === 'Entry') {
                            $fieldValues[] = $this->loadEntry($inner, $contentfulEntry->getSpace());
                        }
                        if ($inner['sys']['linkType'] === 'Asset') {
                            $fieldValues[] = $this->loadAsset($inner, $contentfulEntry->getSpace());
                            $fieldType = 'assets';
                        }
                    }
                    $field->setValue($fieldValues, $fieldType);
                } else {
                    $field->setValue($innerField, 'json');
                }
            } catch (Throwable $t) {
                // Do nothing
            }
        }

        $params['field'] = $field;
    }

    public function isContextual(Block $block): bool
    {
        return true;
    }

    /**
     * @param array $innerField
     */
    private function loadRichText($innerField): NodeInterface
    {
        return $this->richTextParser->parse($innerField);
    }

    /**
     * @param array $innerField
     * @param \Contentful\Delivery\Resource\Space $space
     */
    private function loadEntry($innerField, $space): Result
    {
        $entry = $this->contentful->loadContentfulEntry($space->getId() . '|' . $innerField['sys']['id']);

        return new Result(0, $this->cmsItemBuilder->build($entry));
    }

    /**
     * @param array $innerField
     * @param \Contentful\Delivery\Resource\Space $space
     */
    private function loadAsset($innerField, $space): Asset
    {
        return $this->contentful->loadContentfulAsset($space->getId() . '|' . $innerField['sys']['id']);
    }
}
