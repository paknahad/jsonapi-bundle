<?php

namespace Devleand\JsonApiBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Devleand\JsonApiBundle\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Schema\Document\ResourceDocumentInterface;

class Controller extends AbstractController
{
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    public function __construct(JsonApi $jsonApi, EntityManagerInterface $entityManager, ValidatorInterface $validator, HttpFoundationFactory $httpFoundationFactory)
    {
        $this->jsonApi = $jsonApi;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    protected function jsonApi(): JsonApi
    {
        return $this->jsonApi;
    }

    /**
     * @param mixed                        $object
     * @param Constraint|Constraint[]|null $constraints
     * @param string[]|null                $groups
     */
    protected function validate($object, $constraints = null, $groups = null): void
    {
        $errors = $this->validator->validate($object, $constraints, $groups);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }
    }

    protected function respondOk(ResourceDocumentInterface $document, $object, array $additionalMeta = []): Response
    {
        return $this->respond(
            $this->jsonApi()->respond()->ok($document, $object)
        );
    }

    protected function respondNoContent(): Response
    {
        return $this->respond(
            $this->jsonApi()->respond()->noContent()
        );
    }

    protected function respond(ResponseInterface $response): Response
    {
        return $this->httpFoundationFactory->createResponse($response);
    }
}
