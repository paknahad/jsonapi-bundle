<?php

namespace Devleand\JsonApiBundle\Exception;

use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

class InvalidAttributeException extends AbstractJsonApiValidationException
{
    private $attribute;
    private $value;

    public function __construct(string $attribute, string $value)
    {
        parent::__construct('Invalid value', 422);

        $this->attribute = $attribute;
        $this->value = $value;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Error[]
     */
    protected function getErrors(): array
    {
        return [
            $this->generateValidationError(true, $this->getAttribute(), $this->getValue()),
        ];
    }
}
