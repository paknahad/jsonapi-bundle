<?php

namespace Paknahad\JsonApiBundle\Controller;

use Paknahad\JsonApiBundle\Exception\ValidationException;
use Paknahad\JsonApiBundle\Transformer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Error\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;

class Controller extends AbstractController
{
    private $jsonApi;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(JsonApi $jsonApi, ValidatorInterface $validator)
    {
        $this->jsonApi = $jsonApi;
        $this->validator = $validator;
    }

    protected function jsonApi(): JsonApi
    {
        return $this->jsonApi;
    }

    /**
     * @param mixed $object
     */
    protected function validate($object): void
    {
        $errors = $this->validator->validate($object);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @deprecated This function is deprecated. Use validate() instead.
     */
    protected function validationErrorResponse(ConstraintViolationListInterface $errors): ResponseInterface
    {
        $errorDocument = new ErrorDocument();
        $errorDocument->setJsonApi(new JsonApiObject('1.0'));

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

            $errorDocument->addError($error);
        }

        return $this->jsonApi()->respond()->genericError(
            $errorDocument,
            422
        );
    }
}
