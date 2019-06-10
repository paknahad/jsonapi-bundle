<?= "<?php\ndeclare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use WoohooLabs\Yin\JsonApi\Document\AbstractCollectionDocument;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;
use WoohooLabs\Yin\JsonApi\Schema\Links;

/**
 * <?= $entity_class_name_plural ?> Document.
 */
class <?= $entity_class_name_plural ?>Document extends AbstractCollectionDocument
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
        return Links::createWithoutBaseUri()
            ->setPagination('<?= $route_path ?>', $this->domainObject);
    }
}
