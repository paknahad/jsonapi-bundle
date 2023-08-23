<?php

namespace Devleand\JsonApiBundle;

use Doctrine\Inflector\InflectorFactory;
use Symfony\Bundle\MakerBundle\Str;

class JsonApiStr
{
    public static function entityNameToType(string $entityName): string
    {
        return Str::asTwigVariable(
            self::pluralizeClassName($entityName)
        );
    }

    public static function singularizeClassName(string $entityName): string
    {
        return InflectorFactory::create()->build()->singularize(Str::getShortClassName($entityName));
    }

    public static function pluralizeClassName(string $entityName): string
    {
        return InflectorFactory::create()->build()->pluralize(Str::getShortClassName($entityName));
    }

    public static function genEntityIdName($entityName, bool $withBrackets = false)
    {
        return sprintf(
            $withBrackets ? '{%s}' : '%s',
            self::singularizeClassName($entityName).'_id'
        );
    }
}
