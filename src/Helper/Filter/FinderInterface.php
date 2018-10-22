<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

interface FinderInterface
{
    /**
     * Set request object.
     *
     * @param Request $request
     */
    public function setRequest(Request $request);

    /**
     * Set the QueryBuilder for the finder.
     *
     * @param QueryBuilder $query
     */
    public function setQuery(QueryBuilder $query);

    /**
     * Set the fieldmanager.
     *
     * @param FieldManager $fieldManager
     */
    public function setFieldManager(FieldManager $fieldManager);

    /**
     * Filters the query based on the registered Finders.
     */
    public function filterQuery();
}
