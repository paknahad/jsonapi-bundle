<?php

namespace Devleand\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Devleand\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface FinderInterface.
 */
interface FinderInterface
{
    /**
     * Set request object.
     */
    public function setRequest(Request $request): void;

    /**
     * Set the QueryBuilder for the finder.
     */
    public function setQuery(QueryBuilder $query): void;

    /**
     * Set the fieldmanager.
     */
    public function setFieldManager(FieldManager $fieldManager): void;

    /**
     * Filters the query based on the registered Finders.
     */
    public function filterQuery(): void;

    /**
     * Sort the query based on the registered Finders.
     */
    public function sortQuery(): void;
}
