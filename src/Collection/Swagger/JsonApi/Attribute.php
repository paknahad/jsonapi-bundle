<?php

namespace Devleand\JsonApiBundle\Collection\Swagger\JsonApi;

class Attribute extends AttributeAbstract
{
    public function isPrimaryKey(): bool
    {
        return $this->metadata->has('id') && (bool) ($this->get('id'));
    }

    public function toArray()
    {
        $array = [$this->get('fieldName') => $this->getSwaggerType()];

        return $array;
    }

    private function getSwaggerType()
    {
        switch ($this->get('type')) {
            case 'json':
                return [
                    'type' => 'object',
                ];

            case 'float':
            case 'decimal':
                return [
                    'type' => 'number',
                    'format' => $this->get('type'),
                ];

            case 'smallint':
            case 'bigint':
            case 'integer':
                return [
                    'type' => 'integer',
                    'format' => ('bigint' === $this->get('type')) ? 'int64' : 'int32',
                ];

            case 'date':
            case 'date_immutable':
                return [
                    'type' => 'string',
                    'format' => 'date',
                ];

            case 'datetime':
            case 'datetime_immutable':
            case 'datetimetz':
            case 'datetimetz_immutable':
                return [
                    'type' => 'string',
                    'format' => 'date-time',
                ];

            default:
                return [
                    'type' => $this->get('type'),
                ];
        }
    }
}
