<?php
namespace Paknahad\JsonApiBundle\Helper\Filter;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Helper\FieldManager;
use Symfony\Component\HttpFoundation\Request;

class Finder implements FinderInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

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
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery(QueryBuilder $query) {
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery()
    {
        $this->entityManager = $this->query->getEntityManager();
        $this->fieldManager = new FieldManager();
        $this->fieldManager->setEntityManager($this->entityManager);

        $this->fieldManager->setRootEntity($this->query->getRootEntities()[0]);

        $filters = $this->request->get('filter', []);
        foreach ($filters as $field => $value) {
            $this->setCondition($field, $value);
        }

        $relations = $this->fieldManager->getRelations();
        foreach ($relations as $entity => $relation) {
            if ($entity === $this->fieldManager->getRootEntity()) {
                continue;
            }

            $sourceAlias = FieldManager::ROOT_ALIAS;
            if ($relations[$relation['entity']]['sourceEntity'] != $this->fieldManager->getRootEntity()) {
                $sourceAlias = $relations[$relation['entity']]['alias'];
            }

           $this->query->join(sprintf('%s.%s', $sourceAlias, $relation['entity']), $relation['alias']);
        }
    }

    /**
     * @param string $field
     * @param string $value
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

        if ($fieldMetadata['metadata']['type'] == 'string' && strpos($value, '%') !== false) {
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
}