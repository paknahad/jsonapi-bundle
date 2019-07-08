<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Collection\Swagger;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Bornfight\JsonApiBundle\Collection\Swagger\JsonApi\Attribute;
use Bornfight\JsonApiBundle\Collection\Swagger\JsonApi\Relation;
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

    public static function parse(ClassMetadata $classMetadata): self
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

    public function getFieldsSchema(): array
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

    public function getRelationsSchemas(): array
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

    public function getRelations(): array
    {
        $result = [];
        $this->relations->each(function (string $key, Relation $relation) use (&$result) {
            $result = array_merge($result, $relation->getDefinitionPath());
        });

        return ['properties' => $result];
    }
}
