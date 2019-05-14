<?php

namespace Paknahad\JsonApiBundle\Helper;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class Sorter
{
    /**
     * @var string
     */
    protected $sorting;

    /**
     * @param QueryBuilder $query
     * @param Request      $request
     * @param FieldManager $fieldManager
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function handleQuery(QueryBuilder $query, Request $request, FieldManager $fieldManager)
    {
        $sort = $request->get('sort', null);

        if (empty($sort)) {
            return;
        }

        $sorting = $this->parseSortingString($sort);
        foreach ($sorting as $field) {
            $fieldManager->addField($field['field']);
            $query->addOrderBy($fieldManager->getQueryFieldName($field['field']), $field['direction']);
        }
    }

    /**
     * @param string $sort
     *
     * @return array
     */
    protected function parseSortingString(string $sort): array
    {
        $sorting = [];

        $fields = explode(',', $sort);
        foreach ($fields as $field) {
            $sorting[] = $this->parseField($field);
        }

        return $sorting;
    }

    /**
     * Process an individual field.
     *
     * @param string $field
     *
     * @return array
     */
    protected function parseField(string $field): array
    {
        $data = [
            'field' => $field,
            'direction' => 'ASC',
        ];

        if ('-' !== $field[0]) {
            return $data;
        }

        $data['field'] = ltrim($field, '-');
        $data['direction'] = 'DESC';

        return $data;
    }
}
