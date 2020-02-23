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
     * @param string $entityName
     * @return NodeVisitorAbstract
     */
    public function makeHydratorVisitor(array $propertyNames, string $entityName): NodeVisitorAbstract
    {
        return new HydratorNodeVisitor($propertyNames, $entityName);
    }


}