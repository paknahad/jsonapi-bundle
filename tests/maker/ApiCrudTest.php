<?php

namespace Devleand\JsonApiBundle\Tests\maker;

use Devleand\JsonApiBundle\Maker\ApiCrud;
use Devleand\JsonApiBundle\Test\MakerTestCase;
use Devleand\JsonApiBundle\Test\MakerTestRunner;
use Symfony\Bundle\MakerBundle\Test\MakerTestDetails;

class ApiCrudTest extends MakerTestCase
{
    protected function getMakerClass(): string
    {
        return ApiCrud::class;
    }

    private function createMakeCrudTest(): MakerTestDetails
    {
        return $this->createMakerTest()
            // workaround for segfault in PHP 7.1 CI :/
            ->setRequiredPhpVersion(70200);
    }

    public function getTestDetails()
    {
        yield 'crud_author' => [$this->createMakeCrudTest()
            ->run(function (MakerTestRunner $runner) {
                $runner->copy(
                    'ApiCrud/',
                    ''
                );

                $output = $runner->runMaker([
                    // entity class name
                    'Author',
                ]);

                $this->assertStringContainsString('created: src/Controller/AuthorController.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Document/Author/AuthorDocument.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Document/Author/AuthorsDocument.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Transformer/AuthorResourceTransformer.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Hydrator/Author/AbstractAuthorHydrator.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Hydrator/Author/CreateAuthorHydrator.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Hydrator/Author/UpdateAuthorHydrator.php', $output);
                $this->assertStringContainsString('created: collections/postman.json', $output);
                $this->assertStringContainsString('created: collections/swagger.yaml', $output);
            }),
        ];
        yield 'crud_book_and_author' => [$this->createMakeCrudTest()
            ->run(function (MakerTestRunner $runner) {
                $runner->copy(
                    'ApiCrud/',
                    ''
                );

                $runner->runMaker([
                    // entity class name
                    'Author',
                ]);
                $output = $runner->runMaker([
                    // entity class name
                    'Book',
                ]);

                $this->assertStringContainsString('created: src/Controller/BookController.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Document/Book/BookDocument.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Document/Book/BooksDocument.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Transformer/BookResourceTransformer.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Hydrator/Book/AbstractBookHydrator.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Hydrator/Book/CreateBookHydrator.php', $output);
                $this->assertStringContainsString('created: src/JsonApi/Hydrator/Book/UpdateBookHydrator.php', $output);
                $this->assertStringContainsString('updated: collections/postman.json', $output);
                $this->assertStringContainsString('updated: collections/swagger.yaml', $output);

                $this->runCrudTest($runner, 'GeneratedApiCRUDTest.php');
            }),
        ];
    }

    private function runCrudTest(MakerTestRunner $runner, string $filename)
    {
        $runner->copy(
            'ApiCrud/tests/'.$filename,
            'tests/GeneratedApiCRUDTest.php'
        );

        $runner->configureDatabase();
        $runner->runTests();
    }
}
