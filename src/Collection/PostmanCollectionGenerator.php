<?php

namespace Devleand\JsonApiBundle\Collection;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use const JSON_PRETTY_PRINT;
use Devleand\JsonApiBundle\JsonApiStr;

class PostmanCollectionGenerator extends CollectionGeneratorAbstract
{
    public const POSTMAN_PATH = 'collections/postman.json';

    /**
     * Generate Postman Collection.
     */
    public function generateCollection(ClassMetadataInfo $classMetadata, string $entityName, string $route): string
    {
        $requests = [];
        foreach ($this->getActionsList($entityName) as $name => $action) {
            $requests[] = [
                'name' => $action['title'],
                'request' => [
                    'method' => $action['method'],
                    'header' => [
                        [
                            'key' => 'Content-Type',
                            'value' => 'application/json',
                        ],
                    ],
                    'body' => \in_array($name, ['add', 'edit']) ?
                        $this->generateBody($entityName, $action['method'], $classMetadata) : '',
                    'url' => [
                        'raw' => '{{host}}'.$route.(\in_array($name, ['add', 'list']) ? '/' : '/1'),
                        'host' => [
                            '{{host}}',
                        ],
                        'path' => [
                            $route,
                            \in_array($name, ['add', 'list']) ? '' : '1',
                        ],
                    ],
                ],
            ];
        }

        $directory = [
            'name' => $entityName,
            'description' => '',
            'item' => $requests,
        ];

        $collection = $this->LoadOldCollection();

        $index = $this->alreadyExists($collection, $directory);

        if (null === $index) {
            $collection['item'][] = $directory;
        } else {
            $collection['item'][$index] = $directory;
        }

        $this->fileManager->dumpFile(self::POSTMAN_PATH, json_encode($collection, JSON_PRETTY_PRINT));

        return self::POSTMAN_PATH;
    }

    private function generateBody(string $entityName, string $method, ClassMetadataInfo $classMetadata): array
    {
        $data = [
            'type' => JsonApiStr::entityNameToType($entityName),
        ];

        if ('PATCH' == $method) {
            $data['id'] = '1';
        }

        $data['attributes'] = $this->getAttributes($classMetadata->fieldMappings);
        $data['relationships'] = $this->getRelationships($classMetadata->associationMappings);

        return [
            'mode' => 'raw',
            'raw' => json_encode(['data' => $data], JSON_PRETTY_PRINT),
        ];
    }

    private function getAttributes(array $fields): array
    {
        $attributes = [];

        foreach ($fields as $field) {
            if (isset($field['id']) && $field['id']) {
                continue;
            }

            $attributes[$field['fieldName']] = $field['type'];
        }

        return $attributes;
    }

    private function getRelationships(array $associations): array
    {
        $relationships = [];

        foreach ($associations as $association) {
            $relationData = ['type' => JsonApiStr::entityNameToType($association['targetEntity']), 'id' => '1'];

            if (\in_array($association['type'], [ClassMetadataInfo::TO_MANY, ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::ONE_TO_MANY])) {
                $relationData = [$relationData];
            }

            $relationships[$association['fieldName']] = ['data' => $relationData];
        }

        return $relationships;
    }

    private function loadOldCollection(): array
    {
        if ($this->fileManager->fileExists(self::POSTMAN_PATH)) {
            $collection = json_decode(
                file_get_contents($this->fileManager->absolutizePath(self::POSTMAN_PATH)),
                true
            );
        } else {
            $collection = [
                'info' => [
                    'name' => 'json_api',
                    'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                ],
                'item' => [],
            ];
        }

        return $collection;
    }

    private function alreadyExists(array $collection, array $directory): ?int
    {
        foreach ($collection['item'] as $index => $item) {
            if ($item['name'] === $directory['name']) {
                return $index;
            }
        }

        return null;
    }
}
