<?= "<?php\n" ?>
<?php
    foreach ($associations as $association) {
        if (in_array($association['type'], $to_many_types)) {
            $useManyRelation = true;
        } else {
            $useOneRelation = true;
        }
    }
?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
<?php
    if (isset($useManyRelation)) {
        echo 'use Doctrine\ORM\Query\Expr;
';
    }
?>
use Devleand\JsonApiBundle\Hydrator\AbstractHydrator;
<?php
    if (isset($useOneRelation)) {
        echo 'use Devleand\JsonApiBundle\Exception\InvalidRelationshipValueException;
';
    }
?>
<?php
    if (isset($useManyRelation)) {
        echo 'use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
';
    }

    if (isset($useOneRelation)) {
        echo 'use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
';
    }
?>

/**
 * Abstract <?= $entity_class_name ?> Hydrator.
 */
abstract class Abstract<?= $entity_class_name ?>Hydrator extends AbstractHydrator
{
    /**
     * {@inheritdoc}
     */
    protected function getClass(): string
    {
        return <?= $entity_class_name ?>::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAcceptedTypes(): array
    {
        return ['<?= $entity_type_var_plural ?>'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRelationshipHydrator($<?= $entity_var_name ?>): array
    {
        return [<?php
    foreach ($associations as $association) {
        if (in_array($association['type'], $to_many_types)) {
            ?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>, ToManyRelationship $<?= $association['field_name'] ?>, $data, $relationshipName) {
                $this->validateRelationType($<?= $association['field_name'] ?>, ['<?= $association['target_entity_type']?>']);

                if (count($<?= $association['field_name'] ?>->getResourceIdentifierIds()) > 0) {
                    $association = $this->objectManager->getRepository('<?= $association['target_entity']?>')
                        ->createQueryBuilder('<?= substr($association['field_name'], 0, 1) ?>')
                        ->where((new Expr())->in('<?= substr($association['field_name'], 0, 1) ?>.id', $<?= $association['field_name'] ?>->getResourceIdentifierIds()))
                        ->getQuery()
                        ->getResult();

                    $this->validateRelationValues($association, $<?= $association['field_name'] ?>->getResourceIdentifierIds(), $relationshipName);
                } else {
                    $association = [];
                }

                if ($<?= $entity_var_name ?>-><?= $association['getter'] ?>()->count() > 0) {
                    foreach ($<?= $entity_var_name ?>-><?= $association['getter'] ?>() as $<?= $association['field_name_singular'] ?>) {
                        $<?= $entity_var_name ?>-><?= $association['remover'] ?>($<?= $association['field_name_singular'] ?>);
                    }
                }

                foreach ($association as $<?= $association['field_name_singular'] ?>) {
                    $<?= $entity_var_name ?>-><?= $association['adder'] ?>($<?= $association['field_name_singular'] ?>);
                }
            },<?php
        } else {
            ?>

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> $<?= $entity_var_name ?>, ToOneRelationship $<?= $association['field_name'] ?>, $data, $relationshipName) {
                $this->validateRelationType($<?= $association['field_name'] ?>, ['<?= $association['target_entity_type']?>']);


                $association = null;
                $identifier = $<?= $association['field_name'] ?>->getResourceIdentifier();
                if ($identifier) {
                    $association = $this->objectManager->getRepository('<?= $association['target_entity']?>')
                        ->find($identifier->getId());

                    if (is_null($association)) {
                        throw new InvalidRelationshipValueException($relationshipName, [$identifier->getId()]);
                    }
                }

                $<?= $entity_var_name ?>-><?= $association['setter'] ?>($association);
            },<?php
        }
    }
    ?>

        ];
    }
}
