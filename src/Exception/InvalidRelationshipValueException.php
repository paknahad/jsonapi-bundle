<?php

namespace Devleand\JsonApiBundle\Exception;

use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

class InvalidRelationshipValueException extends AbstractJsonApiValidationException
{
    private $relation;
    private $values;

    public function __construct(string $relation, array $values)
    {
        parent::__construct('Invalid value', 422);

        $this->relation = $relation;
        $this->values = $values;
    }

    public function getRelation()
    {
        return $this->relation;
    }

    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return Error[]
     */
    protected function getErrors(): array
    {
        $errors = [];
        foreach ($this->getValues() as $value) {
            $errors[] = $this->generateValidationError(false, $this->getRelation(), $value);
        }

        return $errors;
    }
}
