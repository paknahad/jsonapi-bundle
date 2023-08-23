<?php

namespace Devleand\JsonApiBundle\Collection;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Devleand\JsonApiBundle\Collection\OpenApi\Attributes;
use Devleand\JsonApiBundle\Collection\OpenApi\OpenApi;
use Devleand\JsonApiBundle\Collection\OpenApi\Paths;
use Symfony\Component\Yaml\Yaml;

class OpenApiCollectionGenerator extends CollectionGeneratorAbstract
{
    /** @var OpenApi */
    private $openApi;
    /** @var Attributes */
    private $fields;

    public const OPEN_API_PATH = 'collections/open_api.yaml';
    public const OPEN_API_TEMPLATE_PATH = __DIR__.'/../Resources/skeleton/open_api.yaml';

    public function generateCollection(ClassMetadataInfo $classMetadata, string $entityName, string $route): string
    {
        $this->setSeed(12345678);

        $this->openApi = new OpenApi($this->loadOldCollection());

        $this->fields = Attributes::parse($classMetadata);

        $this->setSchemas($entityName);
        $this->generateAllPaths($entityName, $route);

        $arrayFile = $this->openApi->toArray();

        ksort($arrayFile['paths']);
        ksort($arrayFile['components']['schemas']);

        $this->fileManager->dumpFile(self::OPEN_API_PATH, Yaml::dump($arrayFile, 20, 2));

        return self::OPEN_API_PATH;
    }

    private function generateAllPaths(string $entityName, string $route): void
    {
        $paths = Paths::buildPaths($this->getActionsList($entityName), $entityName, $route, $this->fields);

        foreach ($paths as $path => $content) {
            $this->openApi->addPath($path, $content);
        }
    }

    private function setSchemas(string $entityName): void
    {
        $this->openApi->addSchema($entityName, $this->fields->getFieldsSchema());

        foreach ($this->fields->getRelationsSchemas() as $name => $schema) {
            $this->openApi->addSchema($name, $schema);
        }
    }

    private function loadOldCollection(): array
    {
        if (file_exists($this->rootDirectory.'/'.self::OPEN_API_PATH)) {
            $file = $this->rootDirectory.'/'.self::OPEN_API_PATH;
        } else {
            $file = self::OPEN_API_TEMPLATE_PATH;
        }

        return Yaml::parseFile($file);
    }
}
