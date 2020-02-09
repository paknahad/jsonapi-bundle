<?php

namespace Paknahad\JsonApiBundle;

use Doctrine\Common\Inflector\Inflector;
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
        return Inflector::singularize(Str::getShortClassName($entityName));
    }

    public static function pluralizeClassName(string $entityName): string
    {
        return Inflector::pluralize(Str::getShortClassName($entityName));
    }

    public static function genEntityIdName($entityName, bool $withBrackets = false)
    {
        return sprintf(
            $withBrackets ? '{%s}' : '%s',
            self::singularizeClassName($entityName).'_id'
        );
    }
}
