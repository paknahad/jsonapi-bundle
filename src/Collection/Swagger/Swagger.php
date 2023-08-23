<?php

namespace Devleand\JsonApiBundle\Collection\Swagger;

use phootwork\collection\ArrayList;
use phootwork\collection\CollectionUtils;
use phootwork\collection\Map;
use phootwork\lang\Arrayable;

class Swagger implements Arrayable
{
    private $content;

    public function __construct(array $collection)
    {
        $this->content = CollectionUtils::toMap($collection);
    }

    public function addDefinition(string $name, array $content): void
    {
        $this->add('definitions', $name, $content);
    }

    public function addPath(string $name, array $content): void
    {
        $this->add('paths', $name, $content);
    }

    private function add(string $name, string $key, array $content): void
    {
        if (!$this->content->has($name)) {
            $this->content->set($name, new Map());
        }

        $this->content->get($name)->remove($key);
        $this->content->get($name)->set($key, $content);
    }

    public function toArray(): array
    {
        return self::mapToArray($this->content);
    }

    /**
     * @param Map|ArrayList $collection
     */
    private function mapToArray($collection): array
    {
        $result = $collection->toArray();

        foreach ($result as $key => &$content) {
            if (\is_object($content)) {
                $content = self::mapToArray($content);
            }
        }

        return $result;
    }
}
