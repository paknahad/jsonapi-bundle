<?php

namespace Devleand\JsonApiBundle\Document;

use WoohooLabs\Yin\JsonApi\Schema\Document\AbstractCollectionDocument as BaseDocument;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;

abstract class AbstractCollectionDocument extends BaseDocument
{
    /**
     * {@inheritdoc}
     */
    public function getJsonApi(): ?JsonApiObject
    {
        return new JsonApiObject('1.0');
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta(): array
    {
        return [];
    }
}
