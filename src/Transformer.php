<?php

namespace Devleand\JsonApiBundle;

use const DATE_ATOM;

class Transformer
{
    public static function Hydrator(string $type, string $fieldName = '$attribute'): string
    {
        switch ($type) {
            case 'date':
            case 'date_immutable':
            case 'datetime':
            case 'datetime_immutable':
            case 'datetimetz':
            case 'datetimetz_immutable':
                return 'new \DateTime('.$fieldName.')';
        }

        return $fieldName;
    }

    public static function ResourceTransform(string $entityName, array $fieldAttributes): string
    {
        switch ($fieldAttributes['type']) {
            case 'date':
            case 'date_immutable':
            case 'datetime':
            case 'datetime_immutable':
            case 'datetimetz':
            case 'datetimetz_immutable':
                return sprintf('$%s->%s()->format(\DATE_ATOM)', $entityName, $fieldAttributes['getter']);
        }

        return sprintf('$%s->%s()', $entityName, $fieldAttributes['getter']);
    }

    public static function validationValueToString($value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return 'Invalid Value';
    }
}
