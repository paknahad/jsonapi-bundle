<?php

namespace Paknahad\JsonApiBundle\Hydrator;

use Doctrine\Common\Persistence\ObjectManager;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;

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
