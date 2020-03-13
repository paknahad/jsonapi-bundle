<?php

namespace Paknahad\JsonApiBundle\EventSubscriber;

use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Throwable;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Response\Responder;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;
use WoohooLabs\Yin\JsonApi\Schema\Link\Link;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;

class JsonApiErrorHandlerEvent implements EventSubscriberInterface
{
    private $environment;

    private $jsonApi;

    private $debug;

    public function __construct($environment, JsonApi $jsonApi, $debug)
    {
        $this->environment = $environment;
        $this->jsonApi = $jsonApi;
        $this->debug = $debug;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exceptionFactory = new DefaultExceptionFactory();

        $exception = $event->getThrowable();

        $httpFoundationFactory = new HttpFoundationFactory();

        $responder = new Responder(
            $this->jsonApi->request,
            $this->jsonApi->response,
            $exceptionFactory,
            new JsonSerializer()
        );

        $additionalMeta = \in_array($this->environment, ['dev', 'test']) || $this->debug === true ? $this->getExceptionMeta($exception) : [];

        $response = $responder->genericError(
            $this->toErrorDocument($exception, $event->getRequest()->getRequestUri()),
            null,
            $additionalMeta
        );

        $event->setResponse($httpFoundationFactory->createResponse($response));
    }

    protected function getExceptionMeta(Throwable $exception): array
    {
        $trace = [];
        foreach ($exception->getTrace() as $item) {
            $trace[] = [
                'file' => $item['file'] ?? $item['class'],
                'line' => $item['line'] ?? 'undefined',
                'function' => $item['function'] ?? 'undefined',
            ];
        }

        return [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $trace,
        ];
    }

    protected function toErrorDocument(Throwable $exception, string $url)
    {
        if ($exception instanceof JsonApiExceptionInterface) {
            return $exception->getErrorDocument();
        }

        $title = 'Internal Server Error';
        $statusCode = 500;

        if ($exception instanceof ValidatorException) {
            $title = $exception->getMessage();
            $statusCode = 422;
        } elseif ($exception instanceof HttpException) {
            $title = $exception->getMessage();
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof AuthenticationException) {
            $title = $exception->getMessage();
            $statusCode = 401;
        }

        /** @var ErrorDocument $errorDocument */
        $errorDocument = new ErrorDocument();
        $errorDocument->setLinks(
            DocumentLinks::createWithoutBaseUri()->setSelf(
                new Link($url)
            )
        )->addError(
            Error::create()
                /* ex. 500 */
                ->setStatus((string) $statusCode)
                /* ex. INTERNAL_SERVER_ERROR */
                ->setCode(preg_replace('/\s+/', '_', strtoupper($title)))
                /* ex. Internal Server Error */
                ->setTitle($title)
        );

        return $errorDocument;
    }
}
