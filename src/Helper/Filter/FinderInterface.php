<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

interface FinderInterface
{
    /**
     * Set request object.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest(Request $request);

    /**
     * Set service entity repository and configure the finder based on the passed repository.
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     */
    public function setQuery(QueryBuilder $query);

    /**
     * @throws EntityNotFoundException
     */
    public function filterQuery();
}
