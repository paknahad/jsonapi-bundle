<?php

namespace Paknahad\JsonApiBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as Base;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use WoohooLabs\Yin\JsonApi\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;

class Controller extends Base
{
    private static $jsonApi;

    /**
     * @return JsonApi
     */
    protected function jsonApi(): JsonApi
    {
        if (! self::$jsonApi) {
            self::$jsonApi = $this->container->get('request_stack')->getCurrentRequest()->get('JsonApi');
        }

        return self::$jsonApi;
    }

    /**
     * @param ConstraintViolationList $errors
     *
     * @return ResponseInterface
     */
    protected function validationErrorResponse(ConstraintViolationList $errors): ResponseInterface
    {
        $errorDocument = new ErrorDocument();
        $errorDocument->setJsonApi(new JsonApiObject('1.0'));

        foreach ($errors as $fieldError) {
            $error = Error::create();
            $pointer = '/data/attributes/' . $fieldError->getPropertyPath();

            $errorSource = new ErrorSource(
                $pointer,
                $fieldError->getInvalidValue() ?? 'Invalid Value'
            );

            $error->setSource($errorSource)
                ->setDetail($fieldError->getMessage())
                ->setStatus('');

            $errorDocument->addError($error);
        }

        return $this->jsonApi()->respond()->genericError(
            $errorDocument,
            [],
            422
        );
    }
}