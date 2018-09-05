<?php

namespace Symfony\Bundle\JsonApiBundle\Tests\Maker;

use Paknahad\JsonApiBundle\JsonApiBundle;
use Paknahad\JsonApiBundle\Test\MakerTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MakerBundle\DependencyInjection\CompilerPass\MakeCommandRegistrationPass;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Bundle\MakerBundle\Test\MakerTestDetails;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ApiCrudTest extends MakerTestCase
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @group functional_group1
     * @dataProvider getCommandTests
     */
    public function testCommands(MakerTestDetails $makerTestDetails)
    {
        $this->executeMakerCommand($makerTestDetails);
    }

    public function getCommandTests()
    {
        yield 'crud_basic' => [
            MakerTestDetails::createTest(
                    $this->getMakerInstance('make_api'),
                    [
                        // entity class name
                        'SweetFood',
                    ]
                )
                ->setFixtureFilesPath(__DIR__.'/../fixtures/ApiCrud')
                // need for crud web tests
                ->configureDatabase()
                ->assert(function (string $output, string $directory) {
                    $this->assertContains('created: src/Controller/SweetFoodController.php', $output);
                    $this->assertContains('created: src/JsonApi/Document/SweetFood/SweetFoodDocument.php', $output);
                    $this->assertContains('created: src/JsonApi/Document/SweetFood/SweetFoodsDocument.php', $output);
                    $this->assertContains('created: src/JsonApi/Transformer/SweetFoodResourceTransformer.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/SweetFood/AbstractSweetFoodHydrator.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/SweetFood/CreateSweetFoodHydrator.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/SweetFood/UpdateSweetFoodHydrator.php', $output);
                    $this->assertContains('created: postman/api_collection.json', $output);
                })
        ];
    }

    private function getMakerInstance(string $serviceName): MakerInterface
    {
        if (null === $this->kernel) {
            $this->kernel = new FunctionalTestKernel('dev', true);
            $this->kernel->boot();
        }

        // a cheap way to guess the service id
        $serviceId = $serviceId ?? sprintf('maker.maker.%s', $serviceName);

        return $this->kernel->getContainer()->get($serviceId);
    }
}

class FunctionalTestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new JsonApiBundle(),
            new MakerBundle()
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->setParameter('kernel.secret', 123);
    }

    public function getRootDir()
    {
        return sys_get_temp_dir().'/'.uniqid('api_maker_', true);
    }

    public function process(ContainerBuilder $container)
    {
        // makes all makers public to help the tests
        foreach ($container->findTaggedServiceIds(MakeCommandRegistrationPass::MAKER_TAG) as $id => $tags) {
            $defn = $container->getDefinition($id);
            $defn->setPublic(true);
        }
    }
}