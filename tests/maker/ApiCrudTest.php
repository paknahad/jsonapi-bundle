<?php

namespace Symfony\Bundle\JsonApiBundle\Tests\Maker;

use Paknahad\JsonApiBundle\JsonApiBundle;
use Paknahad\JsonApiBundle\Test\MakerTestCase;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
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
        yield 'crud_author' => [
            MakerTestDetails::createTest(
                    $this->getMakerInstance('make_api'),
                    [
                        // entity class name
                        'Author'
                    ]
                )
                ->setFixtureFilesPath(__DIR__.'/../fixtures/ApiCrud')
                ->assert(function (string $output, string $directory) {
                    $this->assertContains('created: src/Controller/AuthorController.php', $output);
                    $this->assertContains('created: src/JsonApi/Document/Author/AuthorDocument.php', $output);
                    $this->assertContains('created: src/JsonApi/Document/Author/AuthorsDocument.php', $output);
                    $this->assertContains('created: src/JsonApi/Transformer/AuthorResourceTransformer.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/Author/AbstractAuthorHydrator.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/Author/CreateAuthorHydrator.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/Author/UpdateAuthorHydrator.php', $output);
                    $this->assertContains('created: collections/postman.json', $output);
                    $this->assertContains('created: collections/swagger.yaml', $output);
                })
        ];

        yield 'crud_book' => [
            MakerTestDetails::createTest(
                    $this->getMakerInstance('make_api'),
                    [
                        // entity class name
                        'Book'
                    ]
                )
                ->setFixtureFilesPath(__DIR__.'/../fixtures/ApiCrud')
                // need for crud web tests
                ->configureDatabase()
                ->assert(function (string $output, string $directory) {
                    $this->assertContains('created: src/Controller/BookController.php', $output);
                    $this->assertContains('created: src/JsonApi/Document/Book/BookDocument.php', $output);
                    $this->assertContains('created: src/JsonApi/Document/Book/BooksDocument.php', $output);
                    $this->assertContains('created: src/JsonApi/Transformer/BookResourceTransformer.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/Book/AbstractBookHydrator.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/Book/CreateBookHydrator.php', $output);
                    $this->assertContains('created: src/JsonApi/Hydrator/Book/UpdateBookHydrator.php', $output);
                    $this->assertContains('updated: collections/postman.json', $output);
                    $this->assertContains('updated: collections/swagger.yaml', $output);
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
            new SensioFrameworkExtraBundle(),
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