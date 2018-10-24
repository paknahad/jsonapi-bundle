<?php
namespace Paknahad\JsonApiBundle\Helper;

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

    /**
     * Get relations based on the added fields.
     *
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * Add a field to the Fieldmanager.
     *
     * @param string $fieldName
     *
     * @return array
     *   Field information with relation alias.
     *
     * @throws EntityNotFoundException
     */
    public function addField($fieldName): array
    {
        if (!empty($this->fields[$fieldName])) {
            return $this->fields[$fieldName];
        }

        $this->fields[$fieldName] = $this->parseField($fieldName);

        $this->setRelations($fieldName);
        $relation = $this->relations[$this->fields[$fieldName]['entity']];
        $this->fields[$fieldName]['relation_alias'] = $relation['alias'];

        $this->fields[$fieldName]['metadata'] = $this->getFieldMetaData($this->fields[$fieldName]['entity'], $this->fields[$fieldName]['field']);

        return $this->fields[$fieldName];
    }

    /**
     * Get field name with table alias prefixed for use in a query.
     *
     * @param string $fieldName
     *
     * @return string
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
     *
     * @param string $fieldName
     *
     * @return array|null
     */
    public function getField($fieldName): ?array
    {
        return $this->fields[$fieldName] ?? null;
    }

    /**
     * Parse field string into a separate field and entity.
     *
     * @param string $fieldName
     *
     * @return array
     */
    protected function parseField($fieldName): array
    {
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
     * Get field metadata.
     *
     * @param string $entity
     * @param string $fieldName
     *
     * @return array
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
     *
     * @param string $fieldName
     */
    protected function setRelations(string $fieldName): void
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
     * @param string      $entity
     * @param string|null $sourceEntity
     *
     * @return string
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

        $associations = $this->entityManager->getClassMetadata($sourceEntity)->associationMappings;

        $this->relations[$entity] = [
            'entity' => $associations[$entity]['fieldName'] ?? null,
            'entityClass' => $associations[$entity]['targetEntity'] ?? $entity,
            'sourceEntity' => $sourceEntity,
            'alias' => $alias,
        ];

        return $this->relations[$entity]['entityClass'];
    }
}
