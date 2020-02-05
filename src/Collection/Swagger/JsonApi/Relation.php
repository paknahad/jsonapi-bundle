<?php

namespace Paknahad\JsonApiBundle\Collection\Swagger\JsonApi;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Paknahad\JsonApiBundle\JsonApiStr;

class Relation extends AttributeAbstract
{
    const RELATIONS_SUFFIX = '_relation';

    public function toArray()
    {
        $array = [
            'type' => [
                'type' => 'string',
                'enum' => [JsonApiStr::entityNameToType($this->get('targetEntity'))],
                'example' => JsonApiStr::entityNameToType($this->get('targetEntity')),
            ],
            'id' => [
                'type' => 'integer',
                'minimum' => 1,
                'description' => JsonApiStr::singularizeClassName($this->get('targetEntity')).' ID',
                'example' => rand(2, 99),
            ],
        ];

        return $array;
    }

    public function getDefinitionPath()
    {
        if (\in_array($this->get('type'), [ClassMetadataInfo::TO_MANY, ClassMetadataInfo::MANY_TO_MANY, ClassMetadataInfo::ONE_TO_MANY])) {
            $relation = [
                $this->get('fieldName') => [
                    'type' => 'array',
                    'items' => ['$ref' => '#/definitions/'.$this->generateName()],
                ],
            ];
        } else {
            $relation = [$this->get('fieldName') => ['$ref' => '#/definitions/'.$this->generateName()]];
        }

        return $relation;
    }

    public function generateName()
    {
        return JsonApiStr::singularizeClassName($this->get('targetEntity')).self::RELATIONS_SUFFIX;
    }
}
