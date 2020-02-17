<?php


namespace Paknahad\JsonApiBundle\Maker;

use PhpParser\Node;

class NodeFactory
{

    public function makeString($value): Node\Scalar\String_ {
        return new Node\Scalar\String_($value);
    }


    public function createNewItem($className, $propertyName): Node\Expr\ArrayItem
    {

        $key = new Node\Scalar\String_($className);
        $value = new Node\Expr\Closure();
        $closureParam =
        $type = new Node\Name('Product');
        $functionName = 'get'.ucfirst($className);

        $newAttribute = new Node\Expr\ArrayItem();
    }

}