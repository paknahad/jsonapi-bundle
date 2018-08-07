<?php
namespace Paknahad\JsonApiBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request as JsonApiRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class JsonApiEvent implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $psrFactory = new DiactorosFactory();

        $jsonApiRequest = new JsonApiRequest(
            $psrFactory->createRequest($event->getRequest()),
            new DefaultExceptionFactory()
        );

        $jsonApi = new JsonApi($jsonApiRequest, $psrFactory->createResponse(new Response()));

        $event->getRequest()->query->set('JsonApi', $jsonApi);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}