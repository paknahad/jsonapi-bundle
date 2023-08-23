<?php

namespace Devleand\JsonApiBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;

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
    public const ROOT_ALIAS = 'r';

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
     * @return entityManager
     *                       The EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Sets the EntityManager.
     *
     * @param entityManager $entityManager
     *                                     The EntityManager
     */
    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Gets the RootEntity.
     *
     * @return string
     *                The RootEntity
     */
    public function getRootEntity(): string
    {
        return $this->rootEntity;
    }

    /**
     * Sets the RootEntity.
     *
     * @param string $rootEntity
     *                           The RootEntity
     */
    public function setRootEntity(string $rootEntity): void
    {
        $this->rootEntity = $rootEntity;
    }

    /**
     * Get relations based on the added fields.
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Add a field to the Fieldmanager.
     *
     * @return array
     *               Field information with relation alias
     *
     * @throws EntityNotFoundException
     */
    public function addField(string $fieldName): array
    {
        if (!empty($this->fields[$fieldName])) {
            return $this->fields[$fieldName];
        }

        $this->fields[$fieldName] = $this->parseField($fieldName);
        $this->setRelations($fieldName);

        $relation = $this->getRelevantRelationFromFieldData($this->fields[$fieldName]);
        $this->fields[$fieldName]['relation_alias'] = $relation['alias'];

        $entity = $relation['entity'] ?? $this->fields[$fieldName]['entity'];

        $this->fields[$fieldName]['metadata'] = $this->getFieldMetaData($entity, $this->fields[$fieldName]['field']);

        return $this->fields[$fieldName];
    }

    /**
     * Get field name with table alias prefixed for use in a query.
     */
    public function getQueryFieldName(string $fieldName): string
    {
        return sprintf(
            '%s.%s',
            $this->fields[$fieldName]['relation_alias'] ?? self::ROOT_ALIAS,
            $this->fields[$fieldName]['field']
        );
    }

    /**
     * Get the field data for the requested field.
     */
    public function getField(string $fieldName): ?array
    {
        return $this->fields[$fieldName] ?? null;
    }

    /**
     * Parse field string into a separate field and entity.
     */
    protected function parseField(string $fieldName): array
    {
        $processedField = $this->getProcessedField($fieldName);

        $finalField = array_pop($processedField);
        $entity = !empty($processedField) ? array_shift($processedField) : $this->getRootEntity();

        return [
            'field' => $finalField,
            'entity-path' => $processedField,
            'entity' => $entity,
        ];
    }

    /**
     * Get an indexed array with entities and actual fields separated.
     */
    protected function getProcessedField(string $fieldName): array
    {
        $entity = $this->rootEntity;
        $explodedField = explode('.', $fieldName);
        $numParts = \count($explodedField);

        if (1 === $numParts) {
            return $explodedField;
        }

        $field = [];
        for ($i = 0, $length = $numParts; $i < $length; ++$i) {
            $fields = $this->entityManager->getClassMetadata($entity)->fieldMappings;

            if (!isset($explodedField[$i + 1])) {
                $field[] = $explodedField[$i];
                continue;
            }

            $fieldKey = $explodedField[$i].'.'.$explodedField[$i + 1];
            if (!isset($fields[$fieldKey])) {
                $field[] = $explodedField[$i];

                $relationMetaData = $this->getRelationMetaData($entity, $explodedField[$i]);
                $entity = $relationMetaData['targetEntity'];
                continue;
            }

            $field[] = $explodedField[$i].'.'.$explodedField[$i + 1];
            ++$i;
        }

        return $field;
    }

    /**
     * Get field metadata.
     *
     * @throws EntityNotFoundException
     */
    protected function getFieldMetaData(string $entity, string $fieldName): array
    {
        if (!isset($this->entityFieldMetaData[$entity])) {
            $entityClass = $this->relations[$entity]['entityClass'];
            $this->entityFieldMetaData[$entity] = $this->entityManager->getClassMetadata($entityClass)->fieldMappings;
        }

        if (!isset($this->entityFieldMetaData[$entity][$fieldName])) {
            throw new EntityNotFoundException(sprintf('No entity found for entity %s and field %s', $entity, $fieldName));
        }

        return $this->entityFieldMetaData[$entity][$fieldName];
    }

    /**
     * Set relations for the passed field name.
     */
    protected function setRelations(string $fieldName): void
    {
        $entities = array_merge([$this->fields[$fieldName]['entity']], $this->fields[$fieldName]['entity-path']);

        $sourceEntity = $this->getRootEntity();
        foreach ($entities as $entity) {
            $sourceEntity = $this->setRelation($entity, $sourceEntity);
        }
    }

    /**
     * Set relation & return the class for the relation.
     */
    protected function setRelation(string $entity, string $sourceEntity = null): string
    {
        static $iterator = 1;

        if (isset($this->relations[$entity])) {
            return $this->relations[$entity]['entityClass'];
        }

        $alias = self::ROOT_ALIAS;
        if ($entity !== $this->getRootEntity()) {
            $alias = 'r__'.$iterator++;
        }

        $relationMetaData = $this->getRelationMetaData($sourceEntity, $entity);

        $this->relations[$entity] = [
            'entity' => $relationMetaData['fieldName'] ?? null,
            'entityClass' => $relationMetaData['targetEntity'] ?? $entity,
            'sourceEntity' => $sourceEntity,
            'alias' => $alias,
        ];

        return $this->relations[$entity]['entityClass'];
    }

    /**
     * Get the relation metadata for the provided entity.
     */
    protected function getRelationMetaData(string $sourceEntity, string $entity): array
    {
        $associations = $this->entityManager->getClassMetadata($sourceEntity)->associationMappings;

        return $associations[$entity] ?? [];
    }

    protected function getRelevantRelationFromFieldData(array $fieldData): array
    {
        $entityPath = $fieldData['entity-path'];

        if (0 === \count($entityPath)) {
            return $this->relations[$fieldData['entity']];
        }

        $relevantRelationEntity = $entityPath[\count($entityPath) - 1];

        return $this->relations[$relevantRelationEntity];
    }
}
