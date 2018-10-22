<?php
namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as BasePaginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Paginator
 */
class Paginator
{
    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private $doctrinePaginator;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $size;

    /**
     * @param QueryBuilder $query
     * @param Request $request
     */
    public function handleQuery(QueryBuilder $query, Request $request) {
        $page = $request->get('page', []);
        $this->page = isset($page['number']) ? intval($page['number']) : 1;
        $this->size = isset($page['size']) ? intval($page['size']) : 100;

        $query->setFirstResult(($this->page - 1) * $this->size);
        $query->setMaxResults($this->size);

        $this->doctrinePaginator = new BasePaginator($query, true);

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
        return $this->doctrinePaginator->count();
    }

    public function getDoctrinePaginator()
    {
        return $this->doctrinePaginator;
    }
}