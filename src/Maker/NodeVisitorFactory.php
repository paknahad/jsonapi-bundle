<?php


namespace Paknahad\JsonApiBundle\Maker;

use PhpParser\NodeVisitorAbstract;

class NodeVisitorFactory
{

    /**
     * @param String[] $propertyNames
     * @param array $relations
     * @param string $entityName
     * @return NodeVisitorAbstract
     */
    public function makeTransformerVisitor(array $propertyNames, array $relations, string $entityName): NodeVisitorAbstract
    {

        return new TransformerNodeVisitor($propertyNames, $relations, $entityName);

    }

    /**
     * @param String[] $propertyNames
     * @param array $relations
     * @param string $entityName
     * @param string $hydratorType
     * @return NodeVisitorAbstract
     */
    public function makeHydratorVisitor(array $propertyNames, array $relations, string $entityName, string $hydratorType): NodeVisitorAbstract
    {
        return new HydratorNodeVisitor($propertyNames, $relations, $entityName, $hydratorType);
    }


}