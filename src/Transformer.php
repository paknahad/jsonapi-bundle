<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle;

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
                return sprintf('$%s->%s()->format(DATE_ATOM)', $entityName, $fieldAttributes['getter']);
        }

        return sprintf('$%s->%s()', $entityName, $fieldAttributes['getter']);
    }
}
