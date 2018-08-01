<?php
namespace Paknahad\JsonApiBundle\Lib;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator as BasePaginator;

class Paginator
{
    private $paginator;
    private $page;
    private $size;

    public function __construct(QueryBuilder $query, Request $request)
    {
        $page = $request->get('page', []);
        $this->page = isset($page['number']) ? intval($page['number']) : 1;
        $this->size = isset($page['size']) ? intval($page['size']) : 100;

        $query->setFirstResult(($this->page - 1) * $this->size);
        $query->setMaxResults($this->size);

        $this->paginator = new BasePaginator($query, true);
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getCount()
    {
        return $this->paginator->count();
    }

    public function getPagination()
    {
        return $this->paginator;
    }
}