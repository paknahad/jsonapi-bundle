<?php
namespace Paknahad\JsonApiBundle\EventSubscriber;

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;
use WoohooLabs\Yin\JsonApi\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Request\Request;
use WoohooLabs\Yin\JsonApi\Response\Responder;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;

class JsonApiErrorHandlerEvent implements EventSubscriberInterface
{
    private $environment;

    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $psrFactory = new DiactorosFactory();
        $exceptionFactory = new DefaultExceptionFactory();

        $request = new Request($psrFactory->createRequest($event->getRequest()), $exceptionFactory);
        $responder = new Responder(
            $request,
            $psrFactory->createResponse(new Response()),
            $exceptionFactory,
            new JsonSerializer()
        );

        $additionalMeta = $this->environment === 'dev' ? $this->getExceptionMeta($event->getException()) : [];

        $response = $responder->genericError(
            $this->toErrorDocument($event->getException(), $event->getRequest()->getRequestUri()),
            [],
            null,
            $additionalMeta
        );

        $httpFoundationFactory = new HttpFoundationFactory();

        $event->setResponse($httpFoundationFactory->createResponse($response));
    }

    protected function getExceptionMeta(Throwable $exception): array
    {
        $trace = [];
        foreach ($exception->getTrace() as $item) {
            $trace[] = [
                'file' => $item['file'] ?? $item['class'],
                'line' => $item['line'] ?? 'undefined',
                'function' => $item['function'] ?? 'undefined'
            ];
        }

        return [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $trace
        ];
    }

    protected function toErrorDocument(Throwable $exception, string $url)
    {
        $title = 'Internal Server Error';
        $statusCode = 500;

        if ($exception instanceof HttpException) {
            $title = $exception->getMessage();
            $statusCode = $exception->getStatusCode();
        }

        /** @var ErrorDocument $errorDocument */
        $errorDocument = new ErrorDocument();
        $errorDocument->setLinks(
            Links::createWithoutBaseUri()->setSelf(
                new Link($url)
            )
        )->addError(
            Error::create()
                /* ex. 500 */
                ->setStatus((string)$statusCode)
                /* ex. INTERNAL_SERVER_ERROR */
                ->setCode(preg_replace('/\s+/', '_', strtoupper($title)))
                /* ex. Internal Server Error */
                ->setTitle($title)
        );

        return $errorDocument;
    }
}