<?php

namespace Devleand\JsonApiBundle\Test;

use Nyholm\Psr7\Factory\Psr17Factory;
use Devleand\JsonApiBundle\JsonApiBundle;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakerTestKernel extends \Symfony\Bundle\MakerBundle\Test\MakerTestKernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new JsonApiBundle(),
            new MakerBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 123,
            'router' => [
                'utf8' => true,
            ],
        ]);
        $c->register('nyholm.psr_factory', Psr17Factory::class);
        $c->setAlias(ResponseFactoryInterface::class, 'nyholm.psr_factory');
        $c->setAlias(ServerRequestFactoryInterface::class, 'nyholm.psr_factory');
        $c->setAlias(StreamFactoryInterface::class, 'nyholm.psr_factory');
        $c->setAlias(UploadedFileFactoryInterface::class, 'nyholm.psr_factory');

        $c->register(HttpFoundationFactoryInterface::class, HttpFoundationFactory::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(HttpMessageFactoryInterface::class, PsrHttpFactory::class)->setAutowired(true)->setAutoconfigured(true);
    }
}
