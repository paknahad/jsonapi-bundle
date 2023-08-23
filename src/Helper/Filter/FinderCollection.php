<?php

namespace Devleand\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Devleand\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FinderCollection.
 */
class FinderCollection
{
    /**
     * @var iterable<FinderInterface>
     */
    private $handlers;

    /**
     * FinderCollection constructor.
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handleQuery(QueryBuilder $query, Request $request, FieldManager $fieldManager): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof FinderSupportsInterface && !$handler->supports($request, $fieldManager)) {
                continue;
            }

            $handler->setRequest($request);
            $handler->setQuery($query);
            $handler->setFieldManager($fieldManager);

            $handler->filterQuery();
            $handler->sortQuery();
        }
    }
}
