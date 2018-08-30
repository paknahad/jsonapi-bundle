<?php
namespace Paknahad\JsonApiBundle\Exception;

use Throwable;

class InvalidRelationshipValueException extends \Exception
{
    private $relation;
    private $values;

    public function __construct(string $relation, array $values, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

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
}