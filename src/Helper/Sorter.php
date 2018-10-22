<?php
namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class Sorter
{
    /**
     * @var string
     */
    protected $sorting;

    /**
     * @param QueryBuilder $query
     */
    public function handleQuery(QueryBuilder $query, Request $request) {
        $this->sorting = $request->get('sort', []);
    }

}