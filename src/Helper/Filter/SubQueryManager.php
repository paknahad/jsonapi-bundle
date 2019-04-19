<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\EntityManager;

class SubQueryManager implements SubQueryManagerInterface
{
    const TYPE = 'type';
    const DQL = 'DQL';
    const OPERATOR = 'operator';

    const EQUAL_OPERATOR = 'eq';
    const IN_OPERATOR = 'in';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /** @var array */
    protected $subQueries;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $entityClass, string $subQuery): bool
    {
        return method_exists($this->entityManager->getRepository($entityClass), $this->createMethodName($subQuery));
    }

    /**
     * {@inheritdoc}
     */
    public function generateMetaData(string $entityClass, string $subQuery): array
    {
        if (!isset($this->subQueries[$subQuery])) {
            $this->subQueries[$subQuery] = array_merge(
                $this->entityManager->getRepository($entityClass)->{$this->createMethodName($subQuery)}(),
                [
                    'isSubQuery' => true,
                    'fieldName' => $subQuery,
                    'columnName' => $subQuery,
                ]
            );
        }

        return $this->subQueries[$subQuery];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubQuery(string $subQuery): string
    {
        return sprintf('(%s)', $this->subQueries[$subQuery][self::DQL]);
    }

    private function createMethodName(string $subQueryName): string
    {
        return 'filterBy'.ucfirst($subQueryName);
    }
}
