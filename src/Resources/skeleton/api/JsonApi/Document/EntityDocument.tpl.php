<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Devleand\JsonApiBundle\Document\AbstractSingleResourceDocument;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;
use WoohooLabs\Yin\JsonApi\Schema\Link\Link;

/**
 * <?= $entity_class_name ?> Document.
 */
class <?= $entity_class_name ?>Document extends AbstractSingleResourceDocument
{
    /**
     * {@inheritdoc}
     */
    public function getLinks(): ?DocumentLinks
    {
        return DocumentLinks::createWithoutBaseUri(
            [
                'self' => new Link('<?= $route_path ?>/'.$this->getResourceId()),
            ]
        );
    }
}
