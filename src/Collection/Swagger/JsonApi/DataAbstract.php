<?php

namespace Devleand\JsonApiBundle\Collection\Swagger\JsonApi;

use Devleand\JsonApiBundle\Collection\Swagger\Attributes;
use Devleand\JsonApiBundle\JsonApiStr;

abstract class DataAbstract
{
    /** @var Attributes */
    private $attributes;

    protected $entityName;
    protected $actionName;
    protected $route;

    public function __construct(string $entityName, Attributes $attributes, string $actionName, string $route)
    {
        $this->entityName = $entityName;
        $this->actionName = $actionName;
        $this->attributes = $attributes;
        $this->route = $route;
    }

    abstract public function toArray(): array;

    protected function genJsonApiDataBody(bool $containId = false): array
    {
        if ($containId) {
            $idProperties = [
                'id' => [
                    'type' => 'integer',
                    'format' => 'int64',
                    'example' => 12,
                ],
            ];
        } else {
            $idProperties = [];
        }

        return [
            'type' => 'object',
            'properties' => array_merge(
                $idProperties,
                [
                    'type' => ['type' => 'string', 'example' => JsonApiStr::entityNameToType($this->entityName)],
                    'attributes' => ['$ref' => '#/definitions/'.$this->entityName],
                    'relationships' => $this->attributes->getRelations(),
                ]
            ),
        ];
    }
}
