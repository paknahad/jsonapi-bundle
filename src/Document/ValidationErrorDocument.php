<?php

namespace Devleand\JsonApiBundle\Document;

use Devleand\JsonApiBundle\Transformer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Error\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;

class ValidationErrorDocument extends ErrorDocument
{
    public const STATUS_CODE = 422;

    public function __construct(ConstraintViolationListInterface $errors)
    {
        parent::__construct();
        $this->setJsonApi(new JsonApiObject('1.0'));
        /** @var ConstraintViolation $fieldError */
        foreach ($errors as $fieldError) {
            $error = Error::create();
            $pointer = '/data/attributes/'.$fieldError->getPropertyPath();

            $errorSource = new ErrorSource(
                $pointer,
                Transformer::validationValueToString($fieldError->getInvalidValue())
            );

            $error->setSource($errorSource)
                ->setDetail($fieldError->getMessage())
                ->setStatus('');

            $this->addError($error);
        }
    }

    public function getStatusCode(?int $statusCode = null): int
    {
        return self::STATUS_CODE;
    }
}
