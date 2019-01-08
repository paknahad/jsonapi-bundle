<?php

namespace Paknahad\JsonApiBundle\Collection\Swagger\JsonApi;

use Paknahad\JsonApiBundle\Collection\CollectionGeneratorAbstract;

class Response extends DataAbstract
{
    public function toArray(): array
    {
        $response = [
            'jsonapi' => [
                'type' => 'object',
                'properties' => [
                    'version' => ['type' => 'string', 'example' => '1.0'],
                ],
            ],
            'links' => Link::generateLinks($this->actionName, $this->route),
        ];

        if (CollectionGeneratorAbstract::LIST_ACTION === $this->actionName) {
            $response['data'] = [
                'type' => 'array',
                'items' => $this->genJsonApiDataBody(true),
            ];
        } else {
            $response['data'] = $this->genJsonApiDataBody(true);
        }

        return [
            'type' => 'object',
            'properties' => $response,
        ];
    }
}
