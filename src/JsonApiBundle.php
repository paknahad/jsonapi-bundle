<?php

namespace Paknahad\JsonApiBundle;

use Paknahad\JsonApiBundle\DependencyInjection\Compiler\FinderHandlerPass;
use Paknahad\JsonApiBundle\DependencyInjection\JsonApiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Hamid Paknahad <hp.paknahad@gmail.com>
 */
class JsonApiBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FinderHandlerPass());
    }

    public function getContainerExtension()
    {
        return new JsonApiExtension();
    }
}
