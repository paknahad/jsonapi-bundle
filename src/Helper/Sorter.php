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

    public function handleQuery(QueryBuilder $query, Request $request, FieldManager $fieldManager) {
        $this->sorting = $request->get('sort', []);
    }

}