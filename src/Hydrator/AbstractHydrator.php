<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Hydrator;

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
