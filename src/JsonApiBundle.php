<?php

namespace Devleand\JsonApiBundle;

use Devleand\JsonApiBundle\DependencyInjection\JsonApiExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Hamid Paknahad <hp.paknahad@gmail.com>
 */
class JsonApiBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new JsonApiExtension();
    }
}
