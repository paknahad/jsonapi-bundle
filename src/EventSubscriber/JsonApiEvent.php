<?php
namespace Paknahad\JsonApiBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
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
        $jsonApiRequest = new JsonApiRequest(
            $this->psrRequest($event->getRequest()),
            new DefaultExceptionFactory()
        );

        $jsonApi = new JsonApi($jsonApiRequest, $this->psrResponse());

        $event->getRequest()->query->set('JsonApi', $jsonApi);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    private function psrRequest(Request $request)
    {
        return (new DiactorosFactory())->createRequest($request);
    }

    private function psrResponse()
    {
        return (new DiactorosFactory())->createResponse(new Response());
    }
}