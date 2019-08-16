<?php

namespace Paknahad\JsonApiBundle\Hydrator;

use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractHydrator extends BaseHydrator
{
    protected $objectManager;
    protected $exceptionFactory;

    public function __construct(ObjectManager $objectManager, ExceptionFactoryInterface $exceptionFactory)
    {
        $this->objectManager = $objectManager;
        $this->exceptionFactory = $exceptionFactory;
    }
}
