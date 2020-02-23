<?php


namespace Paknahad\JsonApiBundle\Maker;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class HydratorNodeVisitor extends NodeVisitorAbstract
{
    private const METHOD_NAME = 'getAttributeHydrator';
    private const SETTER_PREFIX = 'set';
    private const TYPE_ABSTRACT = 'abstract';
    private const TYPE_CONCRETE = 'concrete';
    /**
     * @var String[]
     */
    private $propertyNames;
    /**
     * @var string
     */
    private $entityName;
    /**
     * @var string
     */
    private $relations;
    /**
     * @var string
     */
    private $hydratorType;

    public function __construct(array $propertyNames, array $relations, string $entityName, string $hydratorType)
    {
        $this->propertyNames = $propertyNames;
        $this->entityName = $entityName;
        $this->relations = $relations;
        $this->hydratorType = $hydratorType;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === self::METHOD_NAME and $this->hydratorType !== self::TYPE_ABSTRACT) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Return_) {
                    $existingItems = $stmt->expr->items;
                    $newItemNames = $this->filterExistingFields($existingItems, $this->propertyNames);

                    foreach ($newItemNames as $newItemName) {
                        $existingItems[] = $this->createNewAttributeItem($newItemName);
                    }
                    $stmt->expr->items = $existingItems;

                }
            }
        }
    }

    private function createNewAttributeItem(string $newItem): Node\Expr\ArrayItem
    {
        $var = new Node\Expr\Variable(lcfirst($this->entityName));
        $closureParam = new Node\Param($var, null, new Node\Name($this->entityName));
        $functionName = self::SETTER_PREFIX . ucfirst($newItem);
        $expr = new Node\Expr\MethodCall($var, $functionName, [new Node\Expr\Variable('attribute')]);
        $setStmt = new Node\Stmt\Expression($expr);

        $subNodes = [];
        $subNodes['params'] = [$closureParam, new Node\Expr\Variable('attribute'), new Node\Expr\Variable('data'), new Node\Expr\Variable('attributeName')];
        $subNodes['stmts'] = [$setStmt];

        $closure = new Node\Expr\Closure($subNodes);
        return new Node\Expr\ArrayItem($closure, new Node\Scalar\String_($newItem));
    }

    private function filterExistingFields(array $existingItems, array $entityItemNames): array
    {
        $existingItemNames = [];

        foreach ($existingItems as $existingItem) {
            $existingItemNames[] = $existingItem->key->value;
        }

        $newItems = [];
        foreach ($entityItemNames as $entityItemName) {

            if (!in_array($entityItemName, $existingItemNames)) {
                $newItems[] = $entityItemName;
            }
        }

        return $newItems;
    }
}