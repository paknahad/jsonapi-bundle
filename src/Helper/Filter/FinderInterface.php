<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface FinderInterface.
 */
interface FinderInterface
{
    /**
     * Set request object.
     *
     * @param Request $request
     */
    public function setRequest(Request $request): void;

    /**
     * Set the QueryBuilder for the finder.
     *
     * @param QueryBuilder $query
     */
    public function setQuery(QueryBuilder $query): void;

    /**
     * Set the fieldmanager.
     *
     * @param FieldManager $fieldManager
     */
    public function setFieldManager(FieldManager $fieldManager): void;

    /**
     * Filters the query based on the registered Finders.
     */
    public function filterQuery(): void;
}
