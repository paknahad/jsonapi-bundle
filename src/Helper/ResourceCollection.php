<?php
namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use IteratorAggregate;
use Paknahad\JsonApiBundle\Helper\Filter\FinderCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PageBasedPaginationLinkProviderTrait;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PaginationLinkProviderInterface;

/**
 * Resource Collection
 *
 * @package Paknahad\Lib
 */
class ResourceCollection implements IteratorAggregate, PaginationLinkProviderInterface
{
    use PageBasedPaginationLinkProviderTrait;

    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var ServiceEntityRepositoryInterface
     */
    protected $repository;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \Paknahad\JsonApiBundle\Helper\Filter\FinderCollection
     */
    protected $finderCollection;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var Sorter
     */
    protected $sorter;

    /**
     * ResourceCollection constructor.
     *
     * @param RequestStack $requestStack
     * @param FinderCollection $finderCollection
     * @param Paginator $paginator
     * @param Sorter $sorter
     */
    public function __construct(
        RequestStack $requestStack,
        FinderCollection $finderCollection,
        Paginator $paginator,
        Sorter $sorter
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->finderCollection = $finderCollection;
        $this->paginator = $paginator;
        $this->sorter = $sorter;
    }

    /**
     * Gets the Repository.
     *
     * @return ServiceEntityRepositoryInterface
     *   The Repository.
     */
    public function getRepository(): ServiceEntityRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Sets the Repository.
     *
     * @param ServiceEntityRepositoryInterface $repository
     *   The Repository.
     */
    public function setRepository(ServiceEntityRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Gets the Query.
     *
     * @return QueryBuilder
     *   The Query.
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    public function handleIndexRequest() {
        $this->query = $this->generateQuery();

        $this->finderCollection->handleQuery($this->query, $this->request);
        $this->paginator->handleQuery($this->query, $this->request);
        $this->sorter->handleQuery($this->query, $this->request);

    }

    /**
     * Creates a QueryBuilder by EntityRepository and applies requested filters on that
     *
     * @return QueryBuilder
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    protected function generateQuery(): QueryBuilder
    {
        return $this->repository->createQueryBuilder(FieldManager::ROOT_ALIAS);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->paginator->getDoctrinePaginator();
    }

    public function getTotalItems(): int
    {
        return $this->paginator->getCount();
    }

    public function getPage(): int
    {
        return $this->paginator->getPage();
    }

    public function getSize(): int
    {
        return $this->paginator->getSize();
    }
}
