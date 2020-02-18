<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use DateTimeInterface;

final class ContentfulEntryField
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_ASSET = 'asset';
    public const TYPE_ASSETS = 'assets';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_ENTRIES = 'entries';
    public const TYPE_ENTRY = 'entry';
    public const TYPE_GEOLOCATION = 'geolocation';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_JSON = 'json';
    public const TYPE_OBJECT = 'object';
    public const TYPE_RICHTEXT = 'richtext';
    public const TYPE_STRING = 'string';

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    /**
     * @param mixed $innerField
     */
    public function __construct($innerField)
    {
        $this->type = gettype($innerField);

        if ($this->type !== self::TYPE_ARRAY) {
            $this->value = $innerField;
        }

        if ($this->type === self::TYPE_OBJECT) {
            if ($innerField instanceof DateTimeInterface) {
                $this->value = $innerField;
                $this->type = self::TYPE_DATETIME;
            } elseif ($innerField instanceof \Contentful\RichText\Node\Document) {
                $this->value = $innerField;
                $this->type = self::TYPE_RICHTEXT;
            }
        }
    }

    /**
     * Returns the value of the field.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the type of the field.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns if the field has a value.
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /**
     * Sets the value to the field.
     *
     * @param mixed $value
     * @param string $type
     */
    public function setValue($value, string $type): void
    {
        if ($value !== null) {
            $this->value = $value;
            $this->type = $type;
        }
    }
}
