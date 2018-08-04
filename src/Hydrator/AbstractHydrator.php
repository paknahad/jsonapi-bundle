<?php
namespace Paknahad\JsonApiBundle\Hydrator;

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
     * @throws \Exception
     */
    protected function validateRelationType($relation, array $validTypes): void
    {
        if ($relation instanceof ToOneRelationship) {
            if (!in_array($relation->getResourceIdentifier()->getType(), $validTypes, true)) {
                throw new ValidatorException('Invalid type for parent');
            }
        } elseif ($relation instanceof ToManyRelationship) {
            foreach (array_unique($relation->getResourceIdentifierTypes()) as $type) {
                if (!in_array($type, $validTypes, true)) {
                    throw new ValidatorException('Invalid type for parent');
                }
            }
        } else {
            throw new \Exception('Invalid Relation');
        }
    }
}