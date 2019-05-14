<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FinderCollection.
 */
class FinderCollection
{
    /**
     * @var FinderInterface[]
     */
    private $handlers;

    /**
     * FinderCollection constructor.
     *
     * @param iterable $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param QueryBuilder $query
     * @param Request      $request
     * @param FieldManager $fieldManager
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function handleQuery(QueryBuilder $query, Request $request, FieldManager $fieldManager): void
    {
        foreach ($this->handlers as $handler) {
            $handler->setRequest($request);
            $handler->setQuery($query);
            $handler->setFieldManager($fieldManager);

            $handler->filterQuery();
            $handler->sortQuery();
        }
    }
}
