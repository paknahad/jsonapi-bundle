<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use <?= $entity_full_class_name ?>;
use Paknahad\JsonApiBundle\Hydrator\ValidatorTrait;
use Paknahad\JsonApiBundle\Hydrator\AbstractHydrator;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
<?php
    foreach ($associations as $association) {
        if (in_array($association['type'], $to_many_types)) {
            $useManyRelation = true;
        } else {
            $useOneRelation = true;
        }
    }

    if (isset($useManyRelation)) {
        echo 'use Doctrine\ORM\Query\Expr;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
';
    }

    if (isset($useOneRelation)) {
        echo 'use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use Paknahad\JsonApiBundle\Exception\InvalidRelationshipValueException;
';
    }
?>
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

/**
 * Abstract <?= $entity_class_name ?> Hydrator.
 */
abstract class Abstract<?= $entity_class_name ?>Hydrator extends AbstractHydrator
{
    use ValidatorTrait;

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
        $this->validateFields($this->objectManager->getClassMetadata(<?= $entity_class_name ?>::class), $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function setId($<?= $entity_var_name ?>, string $id): void
    {
        if ($id && (string) $<?= $entity_var_name ?>->getId() !== $id) {
            throw new NotFoundHttpException('both ids in url & body bust be same');
        }
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

                $this->validateRelationValues($association, $<?= $association['field_name'] ?>->getResourceIdentifierIds(), $relationshipName);

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


                $association = null;
                $id = $<?= $association['field_name'] ?>->getResourceIdentifier();
                if ($id) {
                    $association = $this->objectManager->getRepository('<?= $association['target_entity']?>')
                        ->find($<?= $association['field_name'] ?>->getResourceIdentifier()->getId());

                    if (is_null($association)) {
                        throw new InvalidRelationshipValueException($relationshipName, [$<?= $association['field_name'] ?>->getResourceIdentifier()->getId()]);
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
