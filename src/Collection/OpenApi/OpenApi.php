<?php

namespace Devleand\JsonApiBundle\Collection\OpenApi;

use phootwork\collection\ArrayList;
use phootwork\collection\CollectionUtils;
use phootwork\collection\Map;
use phootwork\lang\Arrayable;

class OpenApi implements Arrayable
{
    private $content;
    private $components = 'components';
    private $schemas = 'schemas';

    public function __construct(array $collection)
    {
        $this->content = CollectionUtils::toMap($collection);
    }

    public function addSchema(string $name, array $content): void
    {
        if (!$this->content->has($this->components)) {
            $this->content->set($this->components, new Map());
        }
        $components = $this->content->get($this->components);
        if (!$components->has($this->schemas)) {
            $components->set($this->schemas, new Map());
        }
        /** @var Map $schema */
        $schema = $components->get($this->schemas);

        if (!$schema->has($name)) {
            $schema->set($name, new Map());
        }

        $schema->remove($name);
        $schema->set($name, $content);
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
