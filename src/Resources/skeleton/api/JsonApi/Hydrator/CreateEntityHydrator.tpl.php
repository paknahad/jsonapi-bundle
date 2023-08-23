<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;

/**
 * Create <?= $entity_class_name ?> Hydrator.
 */
class Create<?= $entity_class_name ?>Hydrator extends Abstract<?= $entity_class_name ?>Hydrator
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributeHydrator($<?= $entity_var_name ?>): array
    {
        return [<?php
        foreach ($fields as $field) {
            if (isset($field['id']) && $field['id']) {
                continue;
            }
            ?>

            '<?= $field['name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>, $attribute, $data, $attributeName) {
                $<?= $entity_var_name ?>-><?= $field['setter'] ?>(<?= \Devleand\JsonApiBundle\Transformer::Hydrator($field['type'])?>);
            },<?php
        }
        ?>

        ];
    }
}
