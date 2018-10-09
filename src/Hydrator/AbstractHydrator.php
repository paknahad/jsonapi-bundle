<?php
namespace Paknahad\JsonApiBundle\Hydrator;

use Doctrine\ORM\Mapping\Entity;
use Paknahad\JsonApiBundle\Exception\InvalidRelationshipValueException;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;
use Doctrine\Common\Persistence\ObjectManager;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use Symfony\Component\Validator\Exception\ValidatorException;

abstract class AbstractHydrator extends BaseHydrator
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param  ToOneRelationship|ToManyRelationship $relation
     * @param  array                                $validTypes
     *
     * @throws \Exception | ValidatorException
     */
    protected function validateRelationType($relation, array $validTypes): void
    {
        if ($relation instanceof ToOneRelationship) {
            if (!in_array($relation->getResourceIdentifier()->getType(), $validTypes, true)) {
                throw new ValidatorException('Invalid type: ' . $relation->getResourceIdentifier()->getType());
            }
        } elseif ($relation instanceof ToManyRelationship) {
            foreach (array_unique($relation->getResourceIdentifierTypes()) as $type) {
                if (!in_array($type, $validTypes, true)) {
                    throw new ValidatorException('Invalid type: ' . $type);
                }
            }
        } else {
            throw new \Exception('Invalid Relation');
        }
    }

    /**
     * @param array  $availableEntities
     * @param array  $requestedIds
     * @param string $relationshipName
     *
     * @throws InvalidRelationshipValueException
     */
    protected function validateRelationValues(array $availableEntities, array $requestedIds, string $relationshipName)
    {
        if (count($availableEntities) === count($requestedIds)) {
            return;
        }

        $availableIds = [];
        /** @var Entity $availableEntity */
        foreach ($availableEntities as $availableEntity) {
            $availableIds[] = $availableEntity->getId();
        }

        $diff = array_diff($requestedIds, $availableIds);

        throw new InvalidRelationshipValueException($relationshipName, $diff);
    }
}