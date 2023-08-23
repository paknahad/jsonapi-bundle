<?php

namespace Devleand\JsonApiBundle\Hydrator;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;

abstract class AbstractHydrator extends BaseHydrator
{
    use ValidatorTrait;

    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    public function __construct(ObjectManager $objectManager, ExceptionFactoryInterface $exceptionFactory)
    {
        $this->objectManager = $objectManager;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * Should return Entity::class.
     */
    protected function getClass(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateClientGeneratedId(
        string $clientGeneratedId,
        JsonApiRequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory
    ): void {
        if (!empty($clientGeneratedId)) {
            throw $exceptionFactory->createClientGeneratedIdNotSupportedException($request, $clientGeneratedId);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateRequest(JsonApiRequestInterface $request): void
    {
        $this->validateFields($this->objectManager->getClassMetadata($this->getClass()), $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function setId($object, string $id): void
    {
        if ($id && (string) $object->getId() !== $id) {
            throw new NotFoundHttpException('both ids in url & body must be the same');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function generateId(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeHydrator($object): array
    {
        return [];
    }
}
