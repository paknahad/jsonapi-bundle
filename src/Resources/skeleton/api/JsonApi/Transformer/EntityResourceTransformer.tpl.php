<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;

/**
 * <?= $entity_class_name ?> Resource Transformer
 * @package App\Resource
 */
class <?= $entity_class_name ?>ResourceTransformer extends AbstractResourceTransformer
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
        return (string)$<?= $entity_var_name ?>->getId();
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
    public function getLinks($<?= $entity_var_name ?>): ?Links
    {
        return null;
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
                return $<?= $entity_var_name ?>-><?= $field['getter'] ?>();
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
                    ->setData($<?= $entity_var_name ?>-><?= $association['getter'] ?>(), new <?= $association['target_entity_name'] ?>ResourceTransformer());
            },<?php
        } else {?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>) {
                return ToOneRelationship::create()
                    ->setData($<?= $entity_var_name ?>-><?= $association['getter'] ?>(), new <?= $association['target_entity_name'] ?>ResourceTransformer());
            },<?php
        }
    }?>

        ];
    }
}
