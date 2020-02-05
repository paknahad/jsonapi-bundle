<?php

namespace Paknahad\JsonApiBundle\Test\Constraint;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * Constraint that asserts that the json is valid.
 *
 * The json is passed in the constructor.
 */
class IsValidJsonApi extends Constraint
{
    /** @var ValidationResult $result */
    private $result;

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param string $json JSON
     */
    protected function matches($json): bool
    {
        $data = json_decode($json);
        $schema = Schema::fromJsonString(file_get_contents(__DIR__.'/../../Resources/schema/jsonapi-schema.json'));

        $validator = new Validator();

        $this->result = $validator->schemaValidation($data, $schema);

        return $this->result->isValid();
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        $error = $this->result->getFirstError();

        return 'Invalid JsonApi. Error: '.$error->keyword().PHP_EOL;
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
        foreach ($this->result->getFirstError()->subErrors() as $item) {
            var_dump($item);
        }

        return json_encode($this->result->getFirstError()->keywordArgs(), JSON_PRETTY_PRINT).PHP_EOL;
    }
}
