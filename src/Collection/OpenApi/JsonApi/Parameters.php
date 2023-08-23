<?php

namespace Devleand\JsonApiBundle\Collection\OpenApi\JsonApi;

use Devleand\JsonApiBundle\Collection\CollectionGeneratorAbstract;
use Devleand\JsonApiBundle\JsonApiStr;

class Parameters extends DataAbstract
{
    private function hasPathParam()
    {
        return \in_array(
            $this->actionName,
            [
                CollectionGeneratorAbstract::VIEW_ACTION,
                CollectionGeneratorAbstract::DELETE_ACTION,
                CollectionGeneratorAbstract::EDIT_ACTION,
            ]
        );
    }

    private function getPathParams(): ?array
    {
        if (!$this->hasPathParam()) {
            return null;
        }

        return [
            'name' => JsonApiStr::genEntityIdName($this->entityName),
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'integer',
                'format' => 'int64', ],
        ];
    }

    public function toArray(): array
    {
        $params = [];

        if ($param = $this->getPathParams()) {
            $params[] = $param;
        }

        return $params;
    }
}
