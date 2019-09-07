<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use WoohooLabs\Yin\JsonApi\Schema\Document\AbstractCollectionDocument;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;

/**
 * <?= $entity_class_name_plural ?> Document.
 */
class <?= $entity_class_name_plural ?>Document extends AbstractCollectionDocument
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

    /**
     * {@inheritdoc}
     */
    public function getLinks(): ?DocumentLinks
    {
        return DocumentLinks::createWithoutBaseUri()
            ->setPagination('<?= $route_path ?>', $this->object);
    }
}
