<?php


namespace Paknahad\JsonApiBundle\Maker;

use PhpParser\NodeVisitorAbstract;

class NodeFactory
{

    /**
     * @param String[] $propertyNames
     * @param string $entityName
     * @return NodeVisitorAbstract
     */
    public function makeTransformerVisitor(array $propertyNames, string $entityName): NodeVisitorAbstract
    {

        return new TransformerNodeVisitor($propertyNames, $entityName);

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