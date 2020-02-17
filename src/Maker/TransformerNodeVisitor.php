<?php


namespace Paknahad\JsonApiBundle\Maker;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class TransformerNodeVisitor extends NodeVisitorAbstract
{
    private const METHOD_NAME = 'getAttributes';
    private const GETTER_PREFIX = 'get';
    private $className;
    private $propertyNames;

    public function __construct($propertyNames, $className)
    {
        $this->propertyNames = $propertyNames;
        $this->className = $className;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === self::METHOD_NAME) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Return_) {
                    $existingItems = $stmt->expr->items;
                    $existingItemNames = $stmt->expr->items;
                    $newItems = [];

                    foreach ($existingItems as $existingItem) {
                        $existingItemNames[] = $existingItem->key->value;
                    }

                    foreach ($this->propertyNames as $propertyName) {

                        if (!in_array($propertyName, $existingItemNames)) {
                            $newItems[] = $propertyName;
                        }
                    }
                    foreach ($newItems as $newItem) {
                        $newAttribute = $this->createNewItem($newItem);
                        $existingItems[] = $newAttribute;
                    }
                    $stmt->expr->items = $existingItems;


                }
            }
        }
    }

    private function createNewItem(string $newItem): Node\Expr\ArrayItem
    {
        $key = new Node\Scalar\String_($newItem);
        $type = new Node\Name($this->className);
        $var = new Node\Expr\Variable(lcfirst($this->className));
        $closureParam = new Node\Param($var, null, $type);
        $functionName = self::GETTER_PREFIX . ucfirst($newItem);
        $expr = new Node\Expr\MethodCall($var, $functionName);
        $return = new Node\Stmt\Return_($expr);

        $subNodes = [];
        $subNodes['params'] = [$closureParam];
        $subNodes['stmts'] = [$return];

        $closure = new Node\Expr\Closure($subNodes);
        return new Node\Expr\ArrayItem($closure, $key);
    }
}