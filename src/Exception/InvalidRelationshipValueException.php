<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Exception;

use Exception;
use Throwable;

class InvalidRelationshipValueException extends Exception
{
    private $relation;
    private $values;

    public function __construct(string $relation, array $values, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->relation = $relation;
        $this->values = $values;
    }

    public function getRelation(): string
    {
        return $this->relation;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
