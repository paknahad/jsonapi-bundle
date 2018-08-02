<?php
namespace Paknahad\JsonApiBundle;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Str;

class PostmanCollectionGenerator
{
    const POSTMAN_PATH = 'postman/api_collection.json';

    private $fileManager;

    private static $actions = [
        'list' => ['title' => '%s List', 'method' => 'GET'],
        'add' => ['title' => 'Add %s', 'method' => 'POST'],
        'edit' => ['title' => 'Edit %s', 'method' => 'PATCH'],
        'delete' => ['title' => 'Delete %s', 'method' => 'DELETE'],
        'view' => ['title' => 'Get %s', 'method' => 'GET']
    ];

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Generate Postman Collection
     *
     * @param string $entityName
     * @param string $route
     * @param array  $fields
     * @param array  $associations
     */
    public function generatePostmanCollection(string $entityName, string $route, array $fields, array $associations): void
    {
        $requests = [];
        foreach (self::$actions as $name => $action) {
            $requests[] = [
                'name' => sprintf($action['title'], $entityName),
                'request' => [
                    'method' => $action['method'],
                    'header' => [
                        [
                            'key' => 'Content-Type',
                            'value' => 'application/json'
                        ]
                    ],
                    'body' => in_array($name, ['add', 'edit']) ?
                        $this->generateBody($entityName, $action['method'], $fields, $associations) : '',
                    'url' => [
                        'raw' => '{{host}}/' . $route . in_array($name, ['add', 'list']) ? '/' : '/1',
                        'host' => [
                            '{{host}}'
                        ],
                        'path' => [
                            $route,
                            ''
                        ],
                    ]
                ]
            ];
        }

        $directory = [
            'name' => $entityName,
            'description' => '',
            'item' => $requests
        ];

        $collection = $this->getPostmanCollection();

        $collection['item'][] = $directory;

        $this->fileManager->dumpFile(self::POSTMAN_PATH, json_encode($collection, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $entityName
     * @param string $method
     * @param array  $fields
     * @param array  $associations
     *
     * @return array
     */
    private function generateBody(string $entityName, string $method, array $fields, array $associations): array
    {
        $data = [
            'type' => $this->generateTypeName($entityName)
        ];

        if ($method == 'PATCH') {
            $data['id'] = '1';
        }

        $data['relationships'] = $data['attributes'] = [];

        foreach ($fields as $field) {
            if ($field['id']) {
                continue;
            }

            $data['attributes'][$field['name']] = $field['type'];
        }

        foreach ($associations as $association) {
            $relationData = ['type' => $this->generateTypeName($association['field_name']), 'id' => '1'];

            if (in_array($association['type'], [ClassMetadataInfo::TO_MANY, ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::ONE_TO_MANY])) {
                $relationData = [$relationData];
            }

            $data['relationships'][$association['field_name']] = $relationData;
        }

        return [
            'mode' => 'raw',
            'raw' => json_encode(['data' => $data], JSON_PRETTY_PRINT)
        ];
    }

    /**
     * @param string $entityName
     *
     * @return string
     */
    private function generateTypeName(string $entityName): string
    {
        return Str::asTwigVariable(Inflector::pluralize($entityName));
    }

    /**
     * @return array
     */
    private function getPostmanCollection(): array
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
                    'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
                ],
                'item' => []
            ];
        }

        return $collection;
    }
}