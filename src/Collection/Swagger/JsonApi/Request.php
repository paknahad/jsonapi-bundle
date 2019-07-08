<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Collection\Swagger\JsonApi;

use Bornfight\JsonApiBundle\Collection\CollectionGeneratorAbstract;
use Bornfight\JsonApiBundle\JsonApiStr;
use function in_array;

class Request extends DataAbstract
{
    private function hasBody(): bool
    {
        return in_array(
            $this->actionName,
            [
                CollectionGeneratorAbstract::ADD_ACTION,
                CollectionGeneratorAbstract::EDIT_ACTION,
            ]
        );
    }

    private function hasPathParam(): bool
    {
        return in_array(
            $this->actionName,
            [
                CollectionGeneratorAbstract::VIEW_ACTION,
                CollectionGeneratorAbstract::DELETE_ACTION,
                CollectionGeneratorAbstract::EDIT_ACTION,
            ]
        );
    }

    private function getBodyParams(): ?array
    {
        if (!$this->hasBody()) {
            return null;
        }

        return [
            'in' => 'body',
            'name' => 'body',
            'description' => $this->actionName.ucfirst($this->entityName),
            'required' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'data' => $this->genJsonApiDataBody(CollectionGeneratorAbstract::EDIT_ACTION === $this->actionName),
                ],
            ],
        ];
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
            'type' => 'integer',
            'format' => 'int64',
        ];
    }

    public function toArray(): array
    {
        $params = [];

        if ($body = $this->getBodyParams()) {
            $params[] = $body;
        }

        if ($param = $this->getPathParams()) {
            $params[] = $param;
        }

        return $params;
    }
}
