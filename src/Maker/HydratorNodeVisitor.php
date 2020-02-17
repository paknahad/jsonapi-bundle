<?php


namespace Paknahad\JsonApiBundle\Maker;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class HydratorNodeVisitor extends NodeVisitorAbstract
{
    private const METHOD_NAME = 'getAttributeHydrator';
    private const SETTER_PREFIX = 'set';
    /**
     * @var String[]
     */
    private $propertyNames;
    /**
     * @var string
     */
    private $entityName;

    /**
     * HydratorNodeVisitor constructor.
     * @param String[] $propertyNames
     * @param string $entityName
     */
    public function __construct(array $propertyNames, string $entityName)
    {
        $this->propertyNames = $propertyNames;
        $this->entityName = $entityName;
    }

    public function leaveNode(Node $node)
    {
        //TODO implment hydrator visitor
    }
}