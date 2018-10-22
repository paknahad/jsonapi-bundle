<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

class FinderCollection {
    /**
     * @var FinderInterface[]
     */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handleQuery(QueryBuilder $query, Request $request, FieldManager $fieldManager): void {
        foreach ($this->handlers as $handler) {
            $handler->setRequest($request);
            $handler->setQuery($query);
            $handler->setFieldManager($fieldManager);

            $handler->filterQuery();
        }
    }
}