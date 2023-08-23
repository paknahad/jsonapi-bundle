<?php

namespace Devleand\JsonApiBundle\Collection;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Devleand\JsonApiBundle\Collection\Swagger\Attributes;
use Devleand\JsonApiBundle\Collection\Swagger\Paths;
use Devleand\JsonApiBundle\Collection\Swagger\Swagger;
use Symfony\Component\Yaml\Yaml;

class SwaggerCollectionGenerator extends CollectionGeneratorAbstract
{
    /** @var Swagger */
    private $swagger;
    /** @var Attributes */
    private $fields;

    public const SWAGGER_PATH = 'collections/swagger.yaml';
    public const SWAGGER_TEMPLATE_PATH = __DIR__.'/../Resources/skeleton/swagger.yaml';

    public function generateCollection(ClassMetadataInfo $classMetadata, string $entityName, string $route): string
    {
        $this->setSeed(12345678);

        $this->swagger = new Swagger($this->loadOldCollection());

        $this->fields = Attributes::parse($classMetadata);

        $this->setDefinitions($entityName);
        $this->generateAllPaths($entityName, $route);

        $this->fileManager->dumpFile(self::SWAGGER_PATH, Yaml::dump($this->swagger->toArray(), 20, 2));

        return self::SWAGGER_PATH;
    }

    private function generateAllPaths(string $entityName, string $route): void
    {
        $paths = Paths::buildPaths($this->getActionsList($entityName), $entityName, $route, $this->fields);

        foreach ($paths as $path => $content) {
            $this->swagger->addPath($path, $content);
        }
    }

    private function setDefinitions(string $entityName): void
    {
        $this->swagger->addDefinition($entityName, $this->fields->getFieldsSchema());

        foreach ($this->fields->getRelationsSchemas() as $name => $schema) {
            $this->swagger->addDefinition($name, $schema);
        }
    }

    private function loadOldCollection(): array
    {
        if (file_exists($this->rootDirectory.'/'.self::SWAGGER_PATH)) {
            $file = $this->rootDirectory.'/'.self::SWAGGER_PATH;
        } else {
            $file = self::SWAGGER_TEMPLATE_PATH;
        }

        return Yaml::parseFile($file);
    }
}
