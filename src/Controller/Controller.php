<?php

namespace Paknahad\JsonApiBundle\Controller;

use Paknahad\JsonApiBundle\Transformer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationList;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Error\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;

class Controller extends AbstractController
{
    private $jsonApi;

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    protected function jsonApi(): JsonApi
    {
        return $this->jsonApi;
    }

    protected function validationErrorResponse(ConstraintViolationList $errors): ResponseInterface
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
