<?php

namespace Devleand\JsonApiBundle\Exception;

use Exception;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocumentInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Error\ErrorSource;

abstract class AbstractJsonApiValidationException extends Exception implements JsonApiExceptionInterface
{
    /**
     * @return Error[]
     */
    abstract protected function getErrors(): array;

    public function getErrorDocument(): ErrorDocumentInterface
    {
        return new ErrorDocument($this->getErrors());
    }

    protected function generateValidationError(bool $isAttribute, string $name, string $value): Error
    {
        $error = Error::create();
        $pointer = sprintf(
            '/data/%s/%s',
            $isAttribute ? 'attributes' : 'relationships',
            $name
        );

        $errorSource = new ErrorSource(
            $pointer,
            $value
        );

        $error->setSource($errorSource)
            ->setDetail('Invalid value')
            ->setStatus((string) $this->code);

        return $error;
    }
}
