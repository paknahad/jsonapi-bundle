<?= "<?php\ndeclare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use WoohooLabs\Yin\JsonApi\Schema\Document\AbstractSingleResourceDocument;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;
use WoohooLabs\Yin\JsonApi\Schema\Link\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;

/**
 * <?= $entity_class_name ?> Document.
 */
class <?= $entity_class_name ?>Document extends AbstractSingleResourceDocument
{
    /**
     * {@inheritdoc}
     */
    public function getJsonApi(): JsonApiObject
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

    /**
     * {@inheritdoc}
     */
    public function getLinks(): Links
    {
        return Links::createWithoutBaseUri(
            [
                'self' => new Link('<?= $route_path ?>/'.$this->getResourceId()),
            ]
        );
    }
}
