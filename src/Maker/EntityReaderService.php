<?php


namespace Paknahad\JsonApiBundle\Maker;


use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class EntityReaderService
{
    public const TO_MANY_RELATION = 'toMany';
    public const TO_ONE_RELATION = 'toOne';
    private const ENTITY_ID = 'id';

    /**
     * @param string $class
     * @return array
     * @throws Exception
     */
    public function getPropertyNames(string $class): array
    {
        $reflClass = $this->getReflectionClass($class);

        $viableProperties = [];

        foreach ($reflClass->getProperties() as $property) {
            if ($this->isViableProperty($property) and $property->getName() !== self::ENTITY_ID) {
                $viableProperties[] = $property->getName();
            }
        }
        return $viableProperties;
    }

    /**
     * @param string $class
     * @return array
     * @throws Exception
     */
    public function getRelations(string $class): array
    {

        $reflClass = $this->getReflectionClass($class);

        $viableRelations = [];
        foreach ($reflClass->getProperties() as $property) {
            if ($this->isToOneRelation($property) and $property->getName() !== self::ENTITY_ID) {
                $viableRelations[$property->getName()] = self::TO_ONE_RELATION;
                continue;
            }
            if ($this->isToManyRelation($property) and $property->getName() !== self::ENTITY_ID) {
                $viableRelations[$property->getName()] = self::TO_MANY_RELATION;
            }
        }
        return $viableRelations;
    }

    /**
     * @param string $classPath
     * @return ReflectionClass
     * @throws Exception
     */
    private function getReflectionClass(string $classPath): ReflectionClass
    {
        if (!class_exists($classPath)) {
            throw new Exception(sprintf('Entity Class with class path %s does not exist!', $classPath));
        }

        try {
            $reflClass = new ReflectionClass($classPath);
        } catch (ReflectionException $e) {
            throw new Exception('Could not read entity class. It probably has syntax errors.');
        }
        return $reflClass;
    }

    private function isToOneRelation(ReflectionProperty $property): bool
    {
        $doc = $property->getDocComment();
        preg_match_all('#@(.*?)\n#s', $doc, $annotations);
        $annotations = $annotations[1];

        foreach ($annotations as $annotation) {
            if ($this->matchAnnotation($annotation, 'ManyToOne') or $this->matchAnnotation($annotation, 'OneToOne')) {
                return true;
            }
        }
        return false;
    }

    private function isToManyRelation(ReflectionProperty $property): bool
    {
        $doc = $property->getDocComment();
        preg_match_all('#@(.*?)\n#s', $doc, $annotations);
        $annotations = $annotations[1];

        foreach ($annotations as $annotation) {
            if ($this->matchAnnotation($annotation, 'OneToMany') or $this->matchAnnotation($annotation, 'ManyToMany')) {
                return true;
            }
        }
        return false;
    }


    private function isViableProperty(ReflectionProperty $property): bool
    {
        $doc = $property->getDocComment();
        preg_match_all('#@(.*?)\n#s', $doc, $annotations);
        $annotations = $annotations[1];

        foreach ($annotations as $annotation) {
            if ($this->matchAnnotation($annotation, 'Column')) {
                return true;
            }
        }
        return false;
    }

    private function matchAnnotation(string $annotation, string $expectedType): bool
    {
        return (bool)preg_match_all(sprintf('#ORM\\\%s\(.*?\)#s', $expectedType), $annotation);
    }

}