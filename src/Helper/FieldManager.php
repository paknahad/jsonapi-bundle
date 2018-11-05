<?php
namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Paknahad\JsonApiBundle\Helper\Filter\FinderCollection;
use Symfony\Component\Finder\Finder;

/**
 * Class FieldManager.
 *
 * Use the fieldmanager to register fields required for the query and get metadata on the field for use in the query.
 *
 * Automatically handles required relations based on the field format: entity.field. Relations can be chained by
 * entity.entity.field.
 */
class FieldManager
{
    const ROOT_ALIAS = 'r';

    /**
     * Added fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Metadata for fields, per entity.
     *
     * @var array
     */
    protected $entityFieldMetaData = [];

    /**
     * Relation information.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * @var string
     */
    protected $rootEntity;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Gets the EntityManager.
     *
     * @return EntityManager
     *   The EntityManager.
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Sets the EntityManager.
     *
     * @param EntityManager $entityManager
     *   The EntityManager.
     */
    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Gets the RootEntity.
     *
     * @return string
     *   The RootEntity.
     */
    public function getRootEntity(): string
    {
        return $this->rootEntity;
    }

    /**
     * Sets the RootEntity.
     *
     * @param string $rootEntity
     *   The RootEntity.
     */
    public function setRootEntity(string $rootEntity): void
    {
        $this->rootEntity = $rootEntity;
    }


    public function addField($fieldName) {
        if (!empty($this->fields[$fieldName])) {
            return;
        }

        $this->fields[$fieldName] = $this->parseField($fieldName);

        $this->setRelations($fieldName);
        $relation = $this->relations[$this->fields[$fieldName]['entity']];
        $this->fields[$fieldName]['relation_alias'] = $relation['alias'];

        $this->fields[$fieldName]['metadata'] = $this->getFieldMetaData($this->fields[$fieldName]['entity'], $this->fields[$fieldName]['field']);

        return $this->fields[$fieldName];
    }

    /**
     * @param string $fieldMetadata
     *
     * @return string
     */
    public function getQueryFieldName(string $fieldName): string
    {

        return sprintf(
            '%s.%s',
            $this->fields[$fieldName]['relation_alias'] ?? FieldManager::ROOT_ALIAS,
            $this->fields[$fieldName]['field']
        );
    }

    /**
     * Get the field data for the requested field.
     *
     * @param $fieldName
     *
     * @return array|null
     */
    public function getField($fieldName) {
        return $this->fields[$fieldName] ?? null;
    }

    /**
     * Parse field string into a separate field and entity.
     *
     * @param $fieldName
     *
     * @return array
     */
    protected function parseField($fieldName) {
        $explodedField = explode('.', $fieldName);
        $finalField = array_pop($explodedField);
        $entity = !empty($explodedField) ? array_shift($explodedField) : $this->getRootEntity();

        return [
            'field'       => $finalField,
            'entity-path' => $explodedField,
            'entity'      => $entity,
        ];
    }

    /**
     * @param string $entity
     * @param string $fieldName
     *
     * @return array
     */
    protected function getFieldMetaData(string $entity, string $fieldName): ?array
    {
        if (!isset($this->entityFieldMetaData[$entity])) {
            $entityClass = $this->relations[$entity]['entityClass'];
            $this->entityFieldMetaData[$entity] = $this->entityManager->getClassMetadata($entityClass)->fieldMappings;
        }

        if (!isset($this->entityFieldMetaData[$entity][$fieldName])) {
            throw new EntityNotFoundException();
        }

        return $this->entityFieldMetaData[$entity][$fieldName];
    }

    protected function setRelations(string $fieldName)
    {
        $this->fields[$fieldName];

        $entities = array_merge([$this->fields[$fieldName]['entity']], $this->fields[$fieldName]['entity-path']);

        $sourceEntity = $this->getRootEntity();
        foreach ($entities as $entity) {
            $sourceEntity = $this->setRelation($entity, $sourceEntity);
        }
    }

    /**
     * Set relation & return the class for the relation.
     *
     * @param string $relation
     *
     * @return string
     */
    protected function setRelation(string $entity, string $sourceEntity = null): string
    {
        static $iterator = 1;

        if (isset($this->relations[$entity])) {
            return $this->relations[$entity];
        }

        $alias = FieldManager::ROOT_ALIAS;
        if ($entity !== $this->getRootEntity()) {
            $alias = 'r__' . $iterator++;
        }

        $associations = $this->entityManager->getClassMetadata($sourceEntity)->associationMappings;

        $this->relations[$entity] = [
            'entity' => $associations[$entity]['fieldName'] ?? null,
            'entityClass' => $associations[$entity]['targetEntity'] ?? $entity,
            'sourceEntity' => $sourceEntity,
            'alias' => $alias,
        ];

        return $associations[$entity]['targetEntity'] ?? $entity;
    }

    /**
     * Get relations based on the added fields.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

}