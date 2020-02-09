<?php

namespace Paknahad\JsonApiBundle;

use Paknahad\JsonApiBundle\DependencyInjection\JsonApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Hamid Paknahad <hp.paknahad@gmail.com>
 */
class JsonApiBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new JsonApiExtension();
    }
}
