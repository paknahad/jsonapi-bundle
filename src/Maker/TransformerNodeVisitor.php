<?php


namespace Paknahad\JsonApiBundle\Maker;


use Exception;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class TransformerNodeVisitor extends NodeVisitorAbstract
{
    private const GET_ATTRIBUTES = 'getAttributes';
    private const GET_RELATIONSHIPS = 'getRelationships';
    private const GETTER_PREFIX = 'get';
    private $className;
    private $propertyNames;
    private $relations;
    private $relationTypes;
    private $newUseClasses = [];

    public function __construct($propertyNames, $relations, $className)
    {
        $this->propertyNames = $propertyNames;
        $this->className = $className;
        [$this->relations, $this->relationTypes] = $this->unpackRelations($relations);
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === self::GET_ATTRIBUTES) {
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
        } else if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === self::GET_RELATIONSHIPS) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Return_) {
                    $existingItems = $stmt->expr->items;
                    $newItemNames = $this->filterExistingFields($existingItems, $this->relations);

                    foreach ($newItemNames as $newItemName) {
                        $existingItems[] = $this->createNewRelationItem($newItemName, $this->relationTypes[$newItemName]);
                    }
                    $stmt->expr->items = $existingItems;


                }
            }
        }
    }

    public function afterTraverse(array $nodes)
    {
        $existingUses = [];
        $namespace = null;
        foreach ($nodes as $node){
            if($node instanceof Node\Stmt\Namespace_){
                $namespace = $node;
            }
        }


        foreach ($namespace->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Use_) {
                $existingUses[] = $this->parseUseStatement($stmt);
            }
        }

        foreach ($this->newUseClasses as $newUseClass) {
            if (!in_array($newUseClass, $existingUses)) {
                array_unshift($namespace->stmts, $this->createNewUseClass($newUseClass));
            }
        }

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

    private function createNewAttributeItem(string $newItem): Node\Expr\ArrayItem
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

    private function createNewRelationItem(string $newItem, string $relationType): Node\Expr\ArrayItem
    {
        $type = new Node\Name($this->className);
        $var = new Node\Expr\Variable(lcfirst($this->className));
        $closureParam = new Node\Param($var, null, $type);

        $relationClassName = $this->buildRelationType($newItem, $relationType);

        $expr = new Node\Expr\StaticCall($relationClassName, 'create');
        $functionName = self::GETTER_PREFIX . ucfirst($newItem);
        $returnClosureExpr = new Node\Expr\MethodCall($var, $functionName);
        $returnClosure = new Node\Stmt\Return_($returnClosureExpr);
        $closureUse = new Node\Param($var);
        $subNodes = [];
        $subNodes['uses'] = [$closureUse];
        $subNodes['stmts'] = [$returnClosure];
        $closure = new Node\Expr\Closure($subNodes);
        $relationTransformerName = new Node\Name (ucfirst($newItem) . 'ResourceTransformer');
        $relationTransformerNew = new Node\Expr\New_($relationTransformerName);
        $methodCall = new Node\Expr\MethodCall($expr, 'setDataAsCallable', [$closure, $relationTransformerNew]);
        $methodCall2 = new Node\Expr\MethodCall($methodCall, 'omitDataWhenNotIncluded');

        $return = new Node\Stmt\Return_($methodCall2);
        $key = new Node\Scalar\String_($newItem);
        $subNodes = [];
        $subNodes['params'] = [$closureParam];
        $subNodes['stmts'] = [$return];
        $closure = new Node\Expr\Closure($subNodes);
        return new Node\Expr\ArrayItem($closure, $key);
    }

    private function unpackRelations(array $relationsObject): array
    {
        $relations = [];
        $relationsTypes = [];

        foreach ($relationsObject as $relationName => $relationType) {
            $relations[] = $relationName;
            $relationsTypes[$relationName] = $relationType;
        }
        return [$relations, $relationsTypes];
    }

    private function buildRelationType(string $newItem, string $relationType): Node\Name
    {

        if ($relationType === EntityReaderService::TO_MANY_RELATION) {
            $this->newUseClasses[] = 'ToManyRelationship';
            return new Node\Name('ToManyRelationship');
        }
        if ($relationType === EntityReaderService::TO_ONE_RELATION) {
            $this->newUseClasses[] = 'ToOneRelationship';
            return new Node\Name('ToOneRelationship');
        }
        throw new Exception(sprintf('Could not determine relation type of %s', $newItem));
    }

    private function parseUseStatement(Node\Stmt\Use_ $stmt): string
    {
        return end($stmt->uses[0]->name->parts);
    }

    private function createNewUseClass(string $newUseClass): Node\Stmt\Use_
    {
        $parts = ['WoohooLabs', 'Yin', 'JsonApi', 'Schema', 'Relationship', $newUseClass];
        $name = new Node\Name($parts);
        $uses = [new Node\Stmt\UseUse($name)];
        return new Node\Stmt\Use_($uses);
    }
}