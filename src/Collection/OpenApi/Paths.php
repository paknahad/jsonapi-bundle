<?php

namespace Devleand\JsonApiBundle\Collection\OpenApi;

use Devleand\JsonApiBundle\Collection\OpenApi\JsonApi\Parameters;
use Devleand\JsonApiBundle\Collection\OpenApi\JsonApi\RequestBody;
use Devleand\JsonApiBundle\Collection\OpenApi\JsonApi\Response;
use Devleand\JsonApiBundle\JsonApiStr;

class Paths
{
    public static function buildPaths(array $actions, string $entityName, string $route, Attributes $attributes): array
    {
        $paths = [];

        foreach ($actions as $name => $action) {
            $path = self::generateUrl($route, $name, $entityName);

            $paths[$path][strtolower($action['method'])] = [
                'tags' => [JsonApiStr::entityNameToType($entityName)],
                'summary' => $action['title'],
                'operationId' => $name.ucfirst($entityName),
                'parameters' => (new Parameters($entityName, $attributes, $name, $route))->toArray(),
                'requestBody' => (new RequestBody($entityName, $attributes, $name, $route))->toArray(),
                'responses' => [
                    '200' => [
                        'description' => 'successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => (new Response($entityName, $attributes, $name, $route))->toArray(),
                            ],
                        ],
                    ],
                ],
            ];
            if (0 === \count($paths[$path][strtolower($action['method'])]['parameters'])) {
                unset($paths[$path][strtolower($action['method'])]['parameters']);
            }
            if (0 === \count($paths[$path][strtolower($action['method'])]['requestBody'])) {
                unset($paths[$path][strtolower($action['method'])]['requestBody']);
            }
        }

        return $paths;
    }

    private static function generateUrl($baseRoute, $actionName, $entityName)
    {
        return $baseRoute.
            (
            \in_array($actionName, ['edit', 'delete', 'view']) ?
                '/'.JsonApiStr::genEntityIdName($entityName, true) : ''
            );
    }
}
