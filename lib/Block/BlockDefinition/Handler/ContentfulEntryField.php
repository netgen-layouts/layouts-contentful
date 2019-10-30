<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;


class ContentfulEntryField
{
    /**
     * @var mixed
     */
    private $innerField;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $type;


    /**
     * @param mixed $innerField
     */
    public function __construct($innerField)
    {
        $this->innerField = $innerField;

        $this->type = gettype($innerField);

        $this->value = null;
        if ( $this->type != "array") {
            $this->value = $innerField;
        }

        if ( $this->type == "string") {
            $datetime = date_create_from_format("Y-m-d\TH:iP", $innerField);
            if ($datetime === false) {
                $this->value = $datetime;
                $this->type = "datetime";
            }
        }
    }

    public function isValueSet(): bool {
        return !is_null($this->value);
    }

    /**
     * @param mixed $value
     * @param string $type
     */
    public function setValue($value, $type): void {
        if (!is_null($value)) {
            $this->value = $value;
            $this->type = $type;
        }
    }

}
