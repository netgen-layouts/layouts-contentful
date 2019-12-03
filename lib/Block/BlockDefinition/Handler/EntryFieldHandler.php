<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\Space;
use Contentful\RichText\ParserInterface;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandler;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Item\CmsItemBuilderInterface;
use Netgen\Layouts\Item\CmsItemInterface;
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
     * @var \Contentful\RichText\ParserInterface
     */
    private $richTextParser;

    /**
     * @var \Netgen\Layouts\Item\CmsItemBuilderInterface
     */
    private $cmsItemBuilder;

    public function __construct(
        Contentful $contentful,
        RequestStack $requestStack,
        ParserInterface $parser,
        CmsItemBuilderInterface $cmsItemBuilder
    ) {
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
            ]
        );

        $builder->add(
            'datetime_format',
            ParameterType\TextLineType::class,
            [
                'required' => true,
                'default_value' => 'Y-m-d',
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
                    'ROADMAP' => 'block.contentful_entry_field.map_type.roadmap',
                    'SATELLITE' => 'block.contentful_entry_field.map_type.satellite',
                    'HYBRID' => 'block.contentful_entry_field.map_type.hybrid',
                    'TERRAIN' => 'block.contentful_entry_field.map_type.terrain',
                ],
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

        $fieldIdentifier = $block->getParameter('field_identifier')->getValue();
        if (!is_string($fieldIdentifier) || !$contentfulEntry->has($fieldIdentifier)) {
            return;
        }

        $innerField = $contentfulEntry->get($fieldIdentifier);
        $field = new ContentfulEntryField($innerField);

        if (is_array($innerField) && !$field->hasValue()) {
            $this->setFieldValue($field, $contentfulEntry, $innerField);
        }

        $params['field'] = $field;
    }

    public function isContextual(Block $block): bool
    {
        return true;
    }

    /**
     * Tries to set the correct field value based on the inner field value retrieved from Contentful.
     *
     * @param mixed[] $innerField
     */
    private function setFieldValue(ContentfulEntryField $field, ContentfulEntry $entry, array $innerField): void
    {
        try {
            if (array_key_exists('content', $innerField) && array_key_exists('nodeType', $innerField)) {
                $field->setValue($this->richTextParser->parse($innerField), ContentfulEntryField::TYPE_RICHTEXT);
            } elseif (array_key_exists('lon', $innerField) && array_key_exists('lat', $innerField)) {
                $field->setValue($innerField, ContentfulEntryField::TYPE_GEOLOCATION);
            } elseif (array_key_exists('sys', $innerField)) {
                if ($innerField['sys']['linkType'] === 'Entry') {
                    $field->setValue($this->loadEntry($entry->getSpace(), $innerField['sys']['id']), ContentfulEntryField::TYPE_ENTRY);
                } elseif ($innerField['sys']['linkType'] === 'Asset') {
                    $field->setValue($this->loadAsset($entry->getSpace(), $innerField['sys']['id']), ContentfulEntryField::TYPE_ASSET);
                }
            } elseif (array_keys($innerField) === range(0, count($innerField) - 1)) {
                $fieldValues = [];
                $fieldType = ContentfulEntryField::TYPE_ENTRIES;

                foreach ($innerField as $subField) {
                    if ($subField['sys']['linkType'] === 'Entry') {
                        $fieldValues[] = $this->loadEntry($entry->getSpace(), $subField['sys']['id']);
                    } elseif ($subField['sys']['linkType'] === 'Asset') {
                        $fieldValues[] = $this->loadAsset($entry->getSpace(), $subField['sys']['id']);
                        $fieldType = ContentfulEntryField::TYPE_ASSETS;
                    }
                }

                $field->setValue($fieldValues, $fieldType);
            } else {
                $field->setValue($innerField, ContentfulEntryField::TYPE_JSON);
            }
        } catch (Throwable $t) {
            // Do nothing
        }
    }

    /**
     * Returns the Contentful entry in the form of Netgen Layouts CMS item ready
     * to be rendered by the block template.
     */
    private function loadEntry(Space $space, string $id): CmsItemInterface
    {
        $entry = $this->contentful->loadContentfulEntry($space->getId() . '|' . $id);

        return $this->cmsItemBuilder->build($entry);
    }

    /**
     * Returns the Contentful asset.
     */
    private function loadAsset(Space $space, string $id): Asset
    {
        return $this->contentful->loadContentfulAsset($space->getId() . '|' . $id);
    }
}
