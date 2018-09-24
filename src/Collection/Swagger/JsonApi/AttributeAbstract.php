<?php
namespace Paknahad\JsonApiBundle\Collection\Swagger\JsonApi;

use gossi\swagger\Schema;
use phootwork\collection\CollectionUtils;

abstract class AttributeAbstract
{
    protected $metadata;

    public function __construct(array $metadata)
    {
        $this->metadata = CollectionUtils::toMap($metadata);
    }

    public function get(string $name)
    {
        return $this->metadata->get($name);
    }

    abstract public function toArray();

    public function toSchema()
    {
        return new Schema([
            'type' => 'object',
            'properties' => $this->toArray()
        ]);
    }
}