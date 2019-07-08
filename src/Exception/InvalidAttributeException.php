<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Exception;

use Exception;
use Throwable;

class InvalidAttributeException extends Exception
{
    private $attribute;
    private $value;

    public function __construct(string $attribute, string $value, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->attribute = $attribute;
        $this->value = $value;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
