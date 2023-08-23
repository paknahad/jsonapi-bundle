<?php

namespace Devleand\JsonApiBundle\EventSubscriber;

use Devleand\JsonApiBundle\Document\ValidationErrorDocument;
use Devleand\JsonApiBundle\Exception\ValidationException;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Throwable;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocument;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocumentInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;
use WoohooLabs\Yin\JsonApi\Schema\Link\Link;

class JsonApiErrorHandlerEvent implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $environment;
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var bool
     */
    private $debug;
    /**
     * @var HttpFoundationFactoryInterface
     */
    private $httpFoundationFactory;

    public function __construct(string $environment, bool $debug, JsonApi $jsonApi, HttpFoundationFactoryInterface $httpFoundationFactory)
    {
        $this->environment = $environment;
        $this->jsonApi = $jsonApi;
        $this->debug = $debug;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $additionalMeta = $this->getAdditionalMeta($exception);

        $response = $this->jsonApi->respond()->genericError(
            $this->toErrorDocument($exception, $event->getRequest()->getRequestUri()),
            null,
            $additionalMeta
        );

        $event->setResponse($this->httpFoundationFactory->createResponse($response));
    }

    protected function getExceptionMeta(Throwable $exception): array
    {
        $trace = [];
        foreach ($exception->getTrace() as $item) {
            $trace[] = [
                'file' => $item['file'] ?? $item['class'] ?? 'undefined',
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

    protected function toErrorDocument(Throwable $exception, string $url): ErrorDocumentInterface
    {
        if ($exception instanceof JsonApiExceptionInterface) {
            return $exception->getErrorDocument();
        }

        $title = 'Internal Server Error';
        $statusCode = 500;

        if ($exception instanceof ValidationException) {
            return new ValidationErrorDocument($exception->getViolations());
        } elseif ($exception instanceof ValidatorException) {
            $title = $exception->getMessage();
            $statusCode = 422;
        } elseif ($exception instanceof HttpException) {
            $title = $exception->getMessage();
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof AuthenticationException) {
            $title = $exception->getMessage();
            $statusCode = 401;
        }

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

    private function getAdditionalMeta(Throwable $exception): array
    {
        if ($exception instanceof ValidationException) {
            return [];
        }

        return \in_array($this->environment, ['dev', 'test']) || true === $this->debug ? $this->getExceptionMeta($exception) : [];
    }
}
