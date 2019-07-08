<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Collection\Swagger;

use Bornfight\JsonApiBundle\Collection\Swagger\JsonApi\Request;
use Bornfight\JsonApiBundle\Collection\Swagger\JsonApi\Response;
use Bornfight\JsonApiBundle\JsonApiStr;
use function in_array;

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
                'produces' => ['application/json'],
                'parameters' => (new Request($entityName, $attributes, $name, $route))->toArray(),
                'responses' => [
                    '200' => [
                        'description' => 'successful operation',
                        'schema' => (new Response($entityName, $attributes, $name, $route))->toArray(),
                    ],
                ],
            ];
        }

        return $paths;
    }

    private static function generateUrl($baseRoute, $actionName, $entityName): string
    {
        return $baseRoute.'/'.
            (
                in_array($actionName, ['edit', 'delete', 'view']) ?
                    JsonApiStr::genEntityIdName($entityName, true) : ''
            );
    }
}
