<?php
namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;

class Finder
{
    const ROOT_ALIAS = 'r';

    protected $entityManager;
    protected $query;
    protected $rootEntity;
    protected $filters;
    protected $fields;
    protected $relations = [];

    public function __construct(ServiceEntityRepository $repository, array $filters)
    {
        $this->query = $repository->createQueryBuilder(self::ROOT_ALIAS);
        $this->entityManager = $this->query->getEntityManager();
        $this->filters = $filters;

        $this->rootEntity = $this->query->getRootEntities()[0];

        $this->setAvailableFields($this->rootEntity);
    }

    /**
     * @return QueryBuilder
     *
     * @throws EntityNotFoundException
     */
    public function getFilteredQuery(): QueryBuilder
    {
        foreach ($this->filters as $field => $value) {
            $this->setCondition($field, $value);
        }

        foreach ($this->relations as $sourceEntityAlias => $relations) {
            foreach ($relations as $relation => $destinationEntityAlias) {
                $this->query->join(sprintf('%s.%s', $sourceEntityAlias, $relation), $destinationEntityAlias);
            }
        }

        return $this->query;
    }

    /**
     * @param string $field
     * @param string $value
     *
     * @throws EntityNotFoundException
     */
    protected function setCondition(string $field, string $value): void
    {
        $fieldMetaData = $this->getFieldMetaData($field);

        $this->query->andWhere(sprintf(
            '%s %s %s',
            $this->getFieldName($fieldMetaData),
            $this->getOperator($fieldMetaData, $value),
            $this->setValue($value)
        ));
    }

    /**
     * @param string $fieldName
     *
     * @return array
     * 
     * @throws EntityNotFoundException
     */
    protected function getFieldMetaData(string $fieldName): array
    {
        $explodedField = array_reverse(explode('.', $fieldName));

        $finalField = array_shift($explodedField);
        $entity = $this->rootEntity;

        if (! empty($explodedField)) {
            $alias = null;

            foreach (array_reverse($explodedField) as $relation) {
                $relationMetaData = $this->getRelationMetaData($entity, $relation);
                $alias = $this->setRelation($relation, $alias);
                $entity = $relationMetaData['targetEntity'];
            }

            $this->setAvailableFields($entity);
        }
        
        if (!isset($this->fields[$entity][$finalField])) {
            throw new EntityNotFoundException();
        }

        $fieldMetaData = $this->fields[$entity][$finalField];

        if (isset($alias)) {
            $fieldMetaData['relation_alias'] = $alias;
        }

        return $fieldMetaData;
    }

    /**
     * @param array $fieldMetadata
     *
     * @return string
     */
    protected function getFieldName(array $fieldMetadata): string
    {
        return sprintf(
            '%s.%s',
            $fieldMetadata['relation_alias'] ?? self::ROOT_ALIAS,
            $fieldMetadata['fieldName']
        );
    }

    /**
     * @param array  $fieldMetadata
     * @param string $value
     *
     * @return string
     */
    protected function getOperator(array $fieldMetadata, string &$value): string
    {
        if (strtolower($value) === 'null') {
            $value = null;

            return 'IS NULL';
        }

        if ($fieldMetadata['type'] == 'string' && strpos($value, '%') !== false) {
            return 'LIKE';
        }

        return '=';
    }

    /**
     * Set value & return that parameter name
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function setValue($value): string
    {
        static $iterator = 1;

        if (is_null($value)) {
            return '';
        }

        $paramName = ':P' . $iterator++;

        $this->query->setParameter($paramName, $value);

        return $paramName;
    }

    /**
     * Set relation & return that alias
     *
     * @param string      $relation
     * @param null|string $alias
     *
     * @return string
     */
    protected function setRelation(string $relation, ?string $alias): string
    {
        static $iterator = 1;

        if (is_null($alias)) {
            $alias = self::ROOT_ALIAS;
        }

        if (! isset($this->relations[$alias][$relation])) {
            $newAlias = 'r__' . $iterator++;

            $this->relations[$alias][$relation] = $newAlias;
        }


        return $this->relations[$alias][$relation];
    }

    /**
     * @param string $entity
     */
    protected function setAvailableFields(string $entity): void
    {
        if (isset($this->fields[$entity])) {
            return;
        }

        $this->fields[$entity] = $this->entityManager->getClassMetadata($entity)->fieldMappings;
    }

    /**
     * @param string $entity
     * @param string $relation
     *
     * @return array
     * @throws EntityNotFoundException
     */
    protected function getRelationMetaData(string $entity, string $relation): array
    {
        $associations = $this->entityManager->getClassMetadata($entity)->associationMappings;

        if (! isset($associations[$relation])) {
            throw new EntityNotFoundException();
        }

        return $associations[$relation];
    }
}