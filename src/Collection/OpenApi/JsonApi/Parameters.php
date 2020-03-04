<?php

namespace Paknahad\JsonApiBundle\Collection\OpenApi\JsonApi;

use Paknahad\JsonApiBundle\Collection\CollectionGeneratorAbstract;
use Paknahad\JsonApiBundle\JsonApiStr;

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
