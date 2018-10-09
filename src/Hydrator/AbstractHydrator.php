<?php
namespace Paknahad\JsonApiBundle\Hydrator;

use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractHydrator extends BaseHydrator
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}