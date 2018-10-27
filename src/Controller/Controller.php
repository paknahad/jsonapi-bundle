<?php

namespace Paknahad\JsonApiBundle\Controller;

use Paknahad\JsonApiBundle\Helper\InputOutputManager;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationList;
use WoohooLabs\Yin\JsonApi\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;

abstract class Controller extends AbstractController
{
    private static $jsonApi;

    /** @var InputOutputManager */
    protected $inputOutputManager;

    public function __construct(InputOutputManager $inputOutputManager)
    {
        $this->inputOutputManager = $inputOutputManager;
    }

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