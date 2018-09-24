<?php
namespace Paknahad\JsonApiBundle;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\MakerBundle\Str;

class JsonApiStr
{
    static public function entityNameToType(string $entityName): string
    {
        return Str::asTwigVariable(
            self::pluralizeClassName($entityName)
        );
    }

    static public function singularizeClassName(string $entityName): string
    {
        return Inflector::singularize(Str::getShortClassName($entityName));
    }

    static public function pluralizeClassName(string $entityName): string
    {
        return Inflector::pluralize(Str::getShortClassName($entityName));
    }
    
    static public function genEntityIdName($entityName, bool $withBrackets = false)
    {
        return sprintf(
            $withBrackets ? '{%s}' : '%s',
            self::singularizeClassName($entityName) . '_id'
        );
    }
}