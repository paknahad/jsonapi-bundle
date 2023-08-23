<?php

namespace Devleand\JsonApiBundle\Helper;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as BasePaginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Paginator.
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

    public function handleQuery(QueryBuilder $query, Request $request, FieldManager $fieldManager)
    {
        $page = $request->get('page', []);
        $this->page = isset($page['number']) ? (int) ($page['number']) : 1;
        $this->size = isset($page['size']) ? (int) ($page['size']) : 100;

        $query->setFirstResult(($this->page - 1) * $this->size);
        $query->setMaxResults($this->size);

        $this->doctrinePaginator = new BasePaginator($query, true);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getCount(): int
    {
        return $this->doctrinePaginator->count();
    }

    public function getDoctrinePaginator(): ?BasePaginator
    {
        return $this->doctrinePaginator;
    }
}
