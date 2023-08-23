<?php

namespace Devleand\JsonApiBundle\Test\Constraint;

use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use const PHP_EOL;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * Constraint that asserts that the json is valid.
 *
 * The json is passed in the constructor.
 */
class IsValidJsonApi extends Constraint
{
    /** @var ValidationResult */
    private $result;

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param string $other JSON
     */
    protected function matches($other): bool
    {
        $validator = new Validator();

        $this->result = $validator->validate(
            json_decode($other),
            file_get_contents(__DIR__.'/../../Resources/schema/jsonapi-schema.json')
        );

        return $this->result->isValid();
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'Invalid JsonApi. Error: '.$this->result->error()->keyword().PHP_EOL;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        foreach ($this->result->error()->subErrors() as $item) {
            var_dump($item->message());
        }

        return $this->result->error()->message().PHP_EOL;
    }
}
