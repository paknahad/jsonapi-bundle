<?php

namespace Devleand\JsonApiBundle\Collection\OpenApi\JsonApi;

use Devleand\JsonApiBundle\Collection\CollectionGeneratorAbstract;

class RequestBody extends DataAbstract
{
    private function hasRequestBody()
    {
        return \in_array(
            $this->actionName,
            [
                CollectionGeneratorAbstract::ADD_ACTION,
                CollectionGeneratorAbstract::EDIT_ACTION,
            ]
        );
    }

    private function getRequestBody(): ?array
    {
        if (!$this->hasRequestBody()) {
            return [];
        }

        return [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => $this->genJsonApiDataBody(true),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function toArray(): array
    {
        return $this->getRequestBody();
    }
}
