<?php

namespace Devleand\JsonApiBundle\Collection\Swagger;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Devleand\JsonApiBundle\Collection\Swagger\JsonApi\Attribute;
use Devleand\JsonApiBundle\Collection\Swagger\JsonApi\Relation;
use phootwork\collection\Map;

class Attributes
{
    /** @var Map */
    private $fields;

    /** @var Map */
    private $relations;

    private function __construct()
    {
        $this->fields = new Map();
        $this->relations = new Map();
    }

    public static function parse(ClassMetadataInfo $classMetadata): self
    {
        $attributes = new self();

        foreach ($classMetadata->fieldMappings as $field) {
            $attributes->addField(new Attribute($field));
        }

        foreach ($classMetadata->associationMappings as $relation) {
            $attributes->addRelation(new Relation($relation));
        }

        return $attributes;
    }

    private function addField(Attribute $field): void
    {
        $this->fields->set($field->get('fieldName'), $field);
    }

    private function addRelation(Relation $relation): void
    {
        $this->relations->set($relation->get('fieldName'), $relation);
    }

    public function getFieldsSchema()
    {
        $result = [];
        $this->fields->each(function (string $key, Attribute $field) use (&$result) {
            if (!$field->isPrimaryKey()) {
                $result = array_merge($result, $field->toArray());
            }
        });

        return [
            'type' => 'object',
            'properties' => $result,
        ];
    }

    public function getRelationsSchemas()
    {
        $result = [];
        $this->relations->each(function (string $key, Relation $relation) use (&$result) {
            $result[$relation->generateName()] = [
                'type' => 'object',
                'properties' => $relation->toArray(),
            ];
        });

        return $result;
    }

    public function getRelations()
    {
        $result = [];
        $this->relations->each(function (string $key, Relation $relation) use (&$result) {
            $result = array_merge($result, $relation->getDefinitionPath());
        });

        return ['properties' => $result];
    }
}
