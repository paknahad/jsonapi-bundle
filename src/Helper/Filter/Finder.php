<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
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
     * @param string $field
     * @param string $value
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    protected function setCondition(string $field, string $value): void
    {
        $fieldMetaData = $this->fieldManager->addField($field);

        $this->query->andWhere($this->generateCondition($fieldMetaData, $field, $value));
    }

    /**
     * @param array  $fieldMetaData
     * @param string $field
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    protected function generateCondition(array $fieldMetaData, string $field, string $value)
    {
        $fieldName = $this->fieldManager->getQueryFieldName($field);

        if ('null' === strtolower($value)) {
            return $this->query->expr()->isNull($fieldName);
        }

        $parameter = $this->setValue($value);

        if (isset($fieldMetaData[SubQueryManager::OPERATOR]) &&
            SubQueryManager::IN_OPERATOR === $fieldMetaData[SubQueryManager::OPERATOR]
        ) {

            return $this->query->expr()->in($parameter, $fieldName);
        }

        if ('string' === $fieldMetaData['metadata']['type'] && false !== strpos($value, '%')) {
            return $this->query->expr()->like($fieldName, $parameter);
        }

        return $this->query->expr()->eq($fieldName, $parameter);
    }

    /**
     * Set value & return that parameter name.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function setValue($value): string
    {
        static $iterator = 1;

        $paramName = ':P'.$iterator++;

        $this->query->setParameter($paramName, $value);

        return $paramName;
    }
}
