<?php


namespace Paknahad\JsonApiBundle\Maker;


use Doctrine\Common\Inflector\Inflector;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class HydratorNodeVisitor extends NodeVisitorAbstract
{
    private const ATTRIBUTES_METHOD_NAME = 'getAttributeHydrator';
    private const RELATIONSHIP_METHOD_NAME = 'getRelationshipHydrator';
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

    private $relations;
    /**
     * @var string
     */
    private $hydratorType;
    private $relationTypes;

    public function __construct(array $propertyNames, array $relations, string $entityName, string $hydratorType)
    {
        $this->propertyNames = $propertyNames;
        $this->entityName = $entityName;
        [$this->relations, $this->relationTypes] = $this->unpackRelations($relations);
        $this->hydratorType = $hydratorType;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === self::ATTRIBUTES_METHOD_NAME and $this->hydratorType !== self::TYPE_ABSTRACT) {
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
        if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === self::RELATIONSHIP_METHOD_NAME and $this->hydratorType === self::TYPE_ABSTRACT) {
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

    private function createNewRelationItem(string $relationName, string $relationType): Node\Expr\ArrayItem
    {
        $entityVar = new Node\Expr\Variable(lcfirst($this->entityName));
        $relationshipNameVar = new Node\Expr\Variable('relationshipName');
        $relationVar = new Node\Expr\Variable(lcfirst($relationName));
        $identifier = new Node\Expr\Variable('identifier');
        $association = new Node\Expr\Variable('association');
        $thisVar = new Node\Expr\Variable('this');
        $nullValue = new ConstFetch(new Name('null'));

        $entityParam = new Node\Param($entityVar, null, new Node\Name($this->entityName));
        $relationshipTypeParam = new Node\Param($relationVar, null, 'ToOneRelationship'); //TODO
        $stmts = [];
        $validate = new Node\Expr\MethodCall($thisVar, 'validateRelationType', [$relationVar, new Node\Expr\Array_([new Node\Scalar\String_(Inflector::pluralize($relationName))], [Node\Expr\Array_::KIND_SHORT])]);
        $assocNull = new Node\Expr\Assign($association, $nullValue);
        $identifierStmt = new Node\Expr\Assign($identifier, new Node\Expr\MethodCall($relationVar, 'getResourceIdentifier'));

        $findMethod = new Node\Expr\MethodCall(new Node\Expr\MethodCall(new Node\Expr\PropertyFetch($thisVar, 'objectManager'), 'getRepository', [new Node\Scalar\String_('App\\Entity\\' . ucfirst($relationName))]), 'find', [new Node\Expr\MethodCall($identifier, 'getId')]);

        $assocRepo = new Node\Expr\Assign($association, $findMethod);

        $ifIsNullSubNodes['stmts'] = [new Node\Stmt\Throw_(new Node\Expr\New_(new Name('InvalidRelationshipValueException'), [$relationshipNameVar, new Node\Expr\Array_([new Node\Expr\MethodCall($identifier, 'getId')], [Node\Expr\Array_::KIND_SHORT])]))];
        $ifIsNull = new Node\Stmt\If_(new Node\Expr\FuncCall(new Node\Name('is_null'), [$association]), $ifIsNullSubNodes);

        $ifSubNodes['stmts'] = [new Node\Stmt\Expression($assocRepo), $ifIsNull];

        $ifStmt = new Node\Stmt\If_($identifier, $ifSubNodes);
        $setStmt = new Node\Expr\MethodCall($entityVar, self::SETTER_PREFIX . ucfirst($relationName), [$association]);
        $stmts[] = new Node\Stmt\Expression($validate);
        $stmts[] = new Node\Stmt\Expression($assocNull);
        $stmts[] = new Node\Stmt\Expression($identifierStmt);
        $stmts[] = $ifStmt;
        $stmts[] = new Node\Stmt\Expression($setStmt);
        $subNodes = [];
        $subNodes['params'] = [$entityParam, $relationshipTypeParam, new Node\Expr\Variable('data'), $relationshipNameVar];
        $subNodes['stmts'] = $stmts;
        $closure = new Node\Expr\Closure($subNodes);
        return new Node\Expr\ArrayItem($closure, new Node\Scalar\String_($relationName));
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
}