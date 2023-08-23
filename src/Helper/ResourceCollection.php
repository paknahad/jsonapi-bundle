<?php

namespace Devleand\JsonApiBundle\Helper;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use IteratorAggregate;
use Devleand\JsonApiBundle\Helper\Filter\FinderCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PageBasedPaginationLinkProviderTrait;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PaginationLinkProviderInterface;

/**
 * Resource Collection.
 */
class ResourceCollection implements IteratorAggregate, PaginationLinkProviderInterface
{
    use PageBasedPaginationLinkProviderTrait;

    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \Devleand\JsonApiBundle\Helper\Filter\FinderCollection
     */
    protected $finderCollection;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var FieldManager
     */
    protected $fieldManager;

    /**
     * ResourceCollection constructor.
     */
    public function __construct(RequestStack $requestStack, FinderCollection $finderCollection, Paginator $paginator, FieldManager $fieldManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->finderCollection = $finderCollection;
        $this->paginator = $paginator;
        $this->fieldManager = $fieldManager;
    }

    /**
     * Gets the Repository.
     *
     * @return entityRepository
     *                          The Repository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * Sets the Repository And makes query_object.
     *
     * @param entityRepository $repository
     *                                     The Repository
     */
    public function setRepository(EntityRepository $repository): void
    {
        $this->repository = $repository;

        $this->query = $this->generateQuery();
    }

    /**
     * Gets the Query.
     *
     * @return queryBuilder
     *                      The Query
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    /**
     * Process the index request.
     *
     * Handles filtering, sorting, relations and pagination.
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function handleIndexRequest()
    {
        $entityManager = $this->query->getEntityManager();
        $this->fieldManager->setEntityManager($entityManager);
        $this->fieldManager->setRootEntity($this->query->getRootEntities()[0]);

        $this->finderCollection->handleQuery($this->query, $this->request, $this->fieldManager);

        $this->addRelationsToQuery();

        // Paginator as the last handler because of how it handles the QueryBuilder any change after this on the
        // QueryBuilder is not included in the final query.
        $this->paginator->handleQuery($this->query, $this->request, $this->fieldManager);
    }

    /**
     * Gets the Paginator.
     *
     * @return Paginator
     *                   The Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    /**
     * Gets the FieldManager.
     *
     * @return FieldManager
     *                      The FieldManager
     */
    public function getFieldManager(): FieldManager
    {
        return $this->fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): DoctrinePaginator
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

    /**
     * Creates a QueryBuilder by EntityRepository and applies requested filters on that.
     */
    protected function generateQuery(): QueryBuilder
    {
        return $this->repository->createQueryBuilder(FieldManager::ROOT_ALIAS);
    }

    /**
     * Add required relations to the query based on the registered fields.
     */
    protected function addRelationsToQuery()
    {
        $relations = $this->fieldManager->getRelations();

        $formattedRelations = $this->getFormattedRelations($relations);

        foreach ($relations as $entity => $relation) {
            if ($entity === $this->fieldManager->getRootEntity()) {
                continue;
            }

            $sourceAlias = FieldManager::ROOT_ALIAS;
            $relationSourceEntity = $relations[$relation['entity']]['sourceEntity'];
            if ($relationSourceEntity !== $this->fieldManager->getRootEntity()) {
                $sourceAlias = $formattedRelations[$relationSourceEntity]['alias'];
            }

            $this->query->leftJoin(sprintf('%s.%s', $sourceAlias, $relation['entity']), $relation['alias']);
        }
    }

    /**
     * Format relations to use entityClass as array key.
     */
    protected function getFormattedRelations(array $relations): array
    {
        $formattedRelations = [];

        foreach ($relations as $relation) {
            $formattedRelations[$relation['entityClass']] = $relation;
        }

        return $formattedRelations;
    }
}
