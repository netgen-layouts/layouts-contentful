<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use Contentful\RichText\Node\Document;
use DateTimeInterface;

use function gettype;

final class ContentfulEntryField
{
    private mixed $value;

    private ContentfulEntryFieldType $type;

    public function __construct(mixed $innerField)
    {
        $this->type = ContentfulEntryFieldType::from(gettype($innerField));

        if ($this->type !== ContentfulEntryFieldType::ARRAY) {
            $this->value = $innerField;
        }

        if ($this->type === ContentfulEntryFieldType::OBJECT) {
            if ($innerField instanceof DateTimeInterface) {
                $this->value = $innerField;
                $this->type = ContentfulEntryFieldType::DATETIME;
            } elseif ($innerField instanceof Document) {
                $this->value = $innerField;
                $this->type = ContentfulEntryFieldType::RICHTEXT;
            }
        }
    }

    /**
     * Returns the value of the field.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Returns the type of the field.
     */
    public function getType(): ContentfulEntryFieldType
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
     */
    public function setValue(mixed $value, ContentfulEntryFieldType $type): void
    {
        if ($value !== null) {
            $this->value = $value;
            $this->type = $type;
        }
    }
}
