<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use Contentful\Core\Api\DateTimeImmutable;
use DateTimeInterface;

final class ContentfulEntryField
{
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

        if ($this->type !== 'array') {
            $this->value = $innerField;
        }

        if ($this->type === 'string') {
            $dateTime = DateTimeImmutable::createFromFormat('Y-m-d\\TH:iP', $innerField);
            if (!$dateTime instanceof DateTimeInterface) {
                $this->value = $dateTime;
                $this->type = 'datetime';
            }
        }
    }

    /**
     * Returns the value of the field.
     *
     * @retun mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the type of the field.
     *
     * @retun mixed
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
