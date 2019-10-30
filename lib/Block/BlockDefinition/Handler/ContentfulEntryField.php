<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use Contentful\Core\Api\DateTimeImmutable;

class ContentfulEntryField
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $type;
    /**
     * @var mixed
     */
    private $innerField;

    /**
     * @param mixed $innerField
     */
    public function __construct($innerField)
    {
        $this->innerField = $innerField;

        $this->type = gettype($innerField);

        $this->value = null;
        if ($this->type !== 'array') {
            $this->value = $innerField;
        }

        if ($this->type === 'string') {
            $datetime = date_create_immutable_from_format('Y-m-d\\TH:iP', $innerField);
            if (!$datetime instanceof DateTimeImmutable) {
                $this->value = $datetime;
                $this->type = 'datetime';
            }
        }
    }

    public function isValueSet(): bool
    {
        return null !== $this->value;
    }

    /**
     * @param mixed $value
     * @param string $type
     */
    public function setValue($value, $type): void
    {
        if (null !== $value) {
            $this->value = $value;
            $this->type = $type;
        }
    }
}
