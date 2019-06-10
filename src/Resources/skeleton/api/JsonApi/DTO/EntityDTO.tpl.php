<?= "<?php\ndeclare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;


class <?= $class_name ?>
{
    <?php
    foreach ($fields as $field) {
        if (isset($field['id']) && $field['id']) {
            continue;
        }
        ?>

    private $<?= $field['name'] ?>;
        <?php
    }
    ?>

    <?php
    foreach ($fields as $field) {
        if (isset($field['id']) && $field['id']) {
            continue;
        }
        ?>

    public function <?= $field['getter'] ?>(): <?= $field['type'] ?>
    {
        return $this-><?=$field['name'] ?>;
    }

    public function <?= $field['setter'] ?>(<?= $field['type'] ?> $value): void
    {
        $this-><?=$field['name'] ?> = $value;
    }
        <?php
    }
    ?>
}
