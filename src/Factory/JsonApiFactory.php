<?php

namespace Devleand\JsonApiBundle\Factory;

use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;

class JsonApiFactory
{
    /**
     * @var PsrHttpFactory
     */
    private $psrFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ExceptionFactoryInterface
     */
    private $exceptionFactory;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(PsrHttpFactory $psrFactory, RequestStack $requestStack, ExceptionFactoryInterface $exceptionFactory, SerializerInterface $serializer)
    {
        $this->psrFactory = $psrFactory;
        $this->requestStack = $requestStack;
        $this->exceptionFactory = $exceptionFactory;
        $this->serializer = $serializer;
    }

    public function create(): JsonApi
    {
        $jsonApiRequest = new JsonApiRequest(
            $this->psrFactory->createRequest($this->requestStack->getCurrentRequest()),
            $this->exceptionFactory
        );

        return new JsonApi($jsonApiRequest, $this->psrFactory->createResponse(new Response()), $this->exceptionFactory, $this->serializer);
    }
}
