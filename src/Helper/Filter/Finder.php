<?php

namespace Devleand\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Devleand\JsonApiBundle\Helper\FieldManager;
use Devleand\JsonApiBundle\Helper\Sorter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Finder.
 */
class Finder implements FinderInterface
{
    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FieldManager
     */
    protected $fieldManager;

    /**
     * @var Sorter
     */
    protected $sorter;

    /**
     * Finder constructor.
     */
    public function __construct(Sorter $sorter)
    {
        $this->sorter = $sorter;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery(QueryBuilder $query): void
    {
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldManager(FieldManager $fieldManager): void
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery(): void
    {
        $filters = $this->request->get('filter', []);
        foreach ($filters as $field => $value) {
            $this->setCondition($field, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sortQuery(): void
    {
        $this->sorter->handleQuery($this->query, $this->request, $this->fieldManager);
    }

    /**
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    protected function setCondition(string $field, string $value): void
    {
        $fieldMetaData = $this->fieldManager->addField($field);

        $this->query->andWhere(sprintf(
            '%s %s %s',
            $this->fieldManager->getQueryFieldName($field),
            $this->getOperator($fieldMetaData, $value),
            $this->setValue($value)
        ));
    }

    protected function getOperator(array $fieldMetadata, string &$value): string
    {
        if ('null' === strtolower($value)) {
            $value = null;

            return 'IS NULL';
        }

        if ('string' === $fieldMetadata['metadata']['type'] && false !== strpos($value, '%')) {
            return 'LIKE';
        }

        return '=';
    }

    /**
     * Set value & return that parameter name.
     *
     * @param mixed $value
     */
    protected function setValue($value): string
    {
        static $iterator = 1;

        if (null === $value) {
            return '';
        }

        $paramName = ':P'.$iterator++;

        $this->query->setParameter($paramName, $value);

        return $paramName;
    }
}
