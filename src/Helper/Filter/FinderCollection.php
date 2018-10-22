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

    /**
     * Creates a QueryBuilder by EntityRepository and makes all registered Finders handle filtering.
     *
     * @param QueryBuilder $query
     * @param Request $request
     */
    public function handleQuery(QueryBuilder $query, Request $request): void {
        foreach ($this->handlers as $handler) {
            $handler->setRequest($request);
            $handler->setQuery($query);

            $handler->filterQuery();
        }
    }
}