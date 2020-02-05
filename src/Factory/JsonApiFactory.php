<?php

namespace Paknahad\JsonApiBundle\Factory;

use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;

class JsonApiFactory
{
    private $psrFactory;

    private $requestStack;

    public function __construct(PsrHttpFactory $psrFactory, RequestStack $requestStack)
    {
        $this->psrFactory = $psrFactory;
        $this->requestStack = $requestStack;
    }

    public function create()
    {
        $jsonApiRequest = new JsonApiRequest(
            $this->psrFactory->createRequest($this->requestStack->getCurrentRequest()),
            new DefaultExceptionFactory()
        );

        return new JsonApi($jsonApiRequest, $this->psrFactory->createResponse(new Response()));
    }
}
