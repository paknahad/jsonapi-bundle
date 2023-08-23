<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Devleand\JsonApiBundle\Document\AbstractCollectionDocument;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;

/**
 * <?= $entity_class_name_plural ?> Document.
 */
class <?= $entity_class_name_plural ?>Document extends AbstractCollectionDocument
{
    /**
     * {@inheritdoc}
     */
    public function getLinks(): ?DocumentLinks
    {
        return DocumentLinks::createWithoutBaseUri()
            ->setPagination('<?= $route_path ?>', $this->object);
    }
}
