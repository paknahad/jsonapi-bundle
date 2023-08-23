<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use WoohooLabs\Yin\JsonApi\Schema\Link\Link;
use WoohooLabs\Yin\JsonApi\Schema\Link\ResourceLinks;
<?php
foreach ($associations as $association) {
    if (in_array($association['type'], $to_many_types)) {
        $useManyRelation = true;
    } else {
        $useOneRelation = true;
    }
}
echo isset($useManyRelation) ? 'use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;' . PHP_EOL : '';
echo isset($useOneRelation) ? 'use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;' . PHP_EOL : '';
?>
use WoohooLabs\Yin\JsonApi\Schema\Resource\AbstractResource;

/**
 * <?= $entity_class_name ?> Resource Transformer.
 */
class <?= $entity_class_name ?>ResourceTransformer extends AbstractResource
{
    /**
     * {@inheritdoc}
     */
    public function getType($<?= $entity_var_name ?>): string
    {
        return '<?= $entity_type_var_plural ?>';
    }

    /**
     * {@inheritdoc}
     */
    public function getId($<?= $entity_var_name ?>): string
    {
        return (string) $<?= $entity_var_name ?>->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta($<?= $entity_var_name ?>): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks($<?= $entity_var_name ?>): ?ResourceLinks
    {
        return ResourceLinks::createWithoutBaseUri()->setSelf(new Link('<?= $route_path ?>/'.$this->getId($<?= $entity_var_name ?>)));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($<?= $entity_var_name ?>): array
    {
        return [<?php
        foreach ($fields as $field) {
            if (isset($field['id']) && $field['id']) {
                continue;
            }
            ?>

            '<?= $field['name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return <?= \Devleand\JsonApiBundle\Transformer::ResourceTransform($entity_var_name, $field)?>;
            },<?php
        }
        ?>

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultIncludedRelationships($<?= $entity_var_name ?>): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($<?= $entity_var_name ?>): array
    {
        return [<?php
    foreach ($associations as $association) {
        if (in_array($association['type'], $to_many_types)) {?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return ToManyRelationship::create()
                    ->setDataAsCallable(
                        function () use ($<?= $entity_var_name ?>) {
                            return $<?= $entity_var_name ?>-><?= $association['getter'] ?>();
                        },
                        new <?= $association['target_entity_name'] ?>ResourceTransformer()
                    )
                    ->omitDataWhenNotIncluded();
            },<?php
        } else {?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return ToOneRelationship::create()
                    ->setDataAsCallable(
                        function () use ($<?= $entity_var_name ?>) {
                            return $<?= $entity_var_name ?>-><?= $association['getter'] ?>();
                        },
                        new <?= $association['target_entity_name'] ?>ResourceTransformer()
                    )
                    ->omitDataWhenNotIncluded();
            },<?php
        }
    }?>

        ];
    }
}
