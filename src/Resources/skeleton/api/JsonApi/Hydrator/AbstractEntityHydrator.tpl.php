<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use Doctrine\ORM\Query\Expr;
use Doctrine\Common\Persistence\ObjectManager;
use Paknahad\JsonApiBundle\Hydrator\AbstractHydrator;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

/**
 * Abstract <?= $entity_class_name ?> Hydrator
 * @package App\JsonApi\Hydrator
 */
abstract class Abstract<?= $entity_class_name ?>Hydrator extends AbstractHydrator
{
    /**
     * {@inheritdoc}
     */
    protected function validateClientGeneratedId(
        string $clientGeneratedId,
        RequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory
    ): void {
        if (!empty($clientGeneratedId)) {
            throw $exceptionFactory->createClientGeneratedIdNotSupportedException(
                $request,
                $clientGeneratedId
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function generateId(): string
    {
        return '';
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
    protected function getAttributeHydrator($<?= $entity_var_name ?>): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function validateRequest(RequestInterface $request): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function setId($<?= $entity_var_name ?>, string $id): ? array
    {
        if ($id) {
            $<?= $entity_var_name ?>['id'] = $id;

            return $<?= $entity_var_name ?>;
        }

        return null;
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

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> &$<?= $entity_var_name ?>, ToManyRelationship $<?= $association['field_name'] ?>, $data, $relationshipName) {
                $this->validateRelationType($<?= $association['field_name'] ?>, ['<?= $association['target_entity_type']?>']);

                $association = $this->objectManager->getRepository('<?= $association['target_entity']?>')
                    ->createQueryBuilder('<?= substr($association['field_name'], 0, 1) ?>')
                    ->where((new Expr())->in('<?= substr($association['field_name'], 0, 1) ?>.id', $<?= $association['field_name'] ?>->getResourceIdentifierIds()))
                    ->getQuery()
                    ->getResult();

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

            '<?= $association['field_name'] ?>' => function (<?= $entity_class_name ?> &$<?= $entity_var_name ?>, ToOneRelationship $<?= $association['field_name'] ?>, $data, $relationshipName) {
                $this->validateRelationType($<?= $association['field_name'] ?>, ['<?= $association['target_entity_type']?>']);

                $association = $this->objectManager->getRepository('<?= $association['target_entity']?>')
                    ->find($<?= $association['field_name'] ?>->getResourceIdentifier()->getId());

                $<?= $entity_var_name ?>-><?= $association['setter'] ?>($association);
            },<?php
        }
    }
    ?>

        ];
    }
}
