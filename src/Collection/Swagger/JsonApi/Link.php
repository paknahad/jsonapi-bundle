<?php

namespace Paknahad\JsonApiBundle\Collection\Swagger\JsonApi;

use Paknahad\JsonApiBundle\Collection\CollectionGeneratorAbstract;

class Link
{
    public static function generateLinks(string $actionName, string $route): array
    {
        $links = [
            'type' => 'object',
            'properties' => [
                'self' => ['type' => 'string', 'example' => $route],
            ],
        ];

        if (CollectionGeneratorAbstract::LIST_ACTION === $actionName) {
            $links['properties'] = [
                'self' => ['type' => 'string', 'example' => $route.'?page[number]=1&page[size]=100'],
                'first' => ['type' => 'string', 'example' => $route.'?page[number]=1&page[size]=100'],
                'last' => ['type' => 'string', 'example' => $route.'?page[number]=1&page[size]=100'],
                'prev' => ['type' => 'string', 'example' => 'null'],
                'next' => ['type' => 'string', 'example' => 'null'],
            ];
        }

        return $links;
    }
}
