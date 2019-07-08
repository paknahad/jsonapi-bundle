<?php
declare(strict_types=1);

namespace Paknahad\JsonApiBundle;

use Paknahad\JsonApiBundle\DependencyInjection\JsonApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JsonApiBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new JsonApiExtension();
    }
}
