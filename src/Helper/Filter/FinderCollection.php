<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class FinderCollection {
    const ROOT_ALIAS = 'r';

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
     * @param ServiceEntityRepositoryInterface          $repository
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function getFilteredQuery(ServiceEntityRepositoryInterface $repository, Request $request): QueryBuilder {
        $queryBuilder = $repository->createQueryBuilder(self::ROOT_ALIAS);

        foreach ($this->handlers as $handler) {
            $handler->setRequest($request);
            $handler->setQuery($queryBuilder);

            $handler->filterQuery();
        }

        return $queryBuilder;
    }
}