<?php

namespace Devleand\JsonApiBundle\Hydrator;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Devleand\JsonApiBundle\Exception\InvalidAttributeException;
use Devleand\JsonApiBundle\Exception\InvalidRelationshipValueException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;

trait ValidatorTrait
{
    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param ToOneRelationship|ToManyRelationship $relation
     *
     * @throws \Exception|ValidatorException
     */
    protected function validateRelationType($relation, array $validTypes): void
    {
        if ($relation instanceof ToOneRelationship) {
            if (!$relation->getResourceIdentifier()) {
                return;
            }

            if (!\in_array($relation->getResourceIdentifier()->getType(), $validTypes, true)) {
                throw new ValidatorException('Invalid type: '.$relation->getResourceIdentifier()->getType());
            }
        } elseif ($relation instanceof ToManyRelationship) {
            if (!$relation->getResourceIdentifiers()) {
                return;
            }

            foreach (array_unique($relation->getResourceIdentifierTypes()) as $type) {
                if (!\in_array($type, $validTypes, true)) {
                    throw new ValidatorException('Invalid type: '.$type);
                }
            }
        } else {
            throw new \Exception('Invalid Relation');
        }
    }

    /**
     * @throws InvalidRelationshipValueException
     */
    protected function validateRelationValues(array $availableEntities, array $requestedIds, string $relationshipName): void
    {
        if (\count($availableEntities) === \count($requestedIds)) {
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

    /**
     * Validate all fields.
     *
     * @throws InvalidAttributeException
     */
    protected function validateFields(ClassMetadata $metadata, JsonApiRequestInterface $request, bool $validExistence = true): void
    {
        $this->validator = Validation::createValidator();

        foreach ($request->getResourceAttributes() as $field => $value) {
            if ($validExistence && !$metadata->hasField($field)) {
                throw new ValidatorException('This attribute does not exist');
            }

            $validator = $this->getValidator($metadata->getTypeOfField($field));

            if (null !== $validator) {
                /** @var ConstraintViolationListInterface $validation */
                $validation = $this->{$validator}($value);
                if ($validation->count() > 0) {
                    throw new InvalidAttributeException($field, $value, $validation->get(0)->getMessage(), 422);
                }
            }
        }
    }

    /**
     * Validate expected relations.
     *
     * @throws RelationshipNotExists
     */
    protected function validateRelations(array $expectedRelation, JsonApiRequestInterface $request)
    {
        foreach ($request->getResource()['relationships'] as $name => $relation) {
            if (!\in_array($name, $expectedRelation)) {
                throw $this->exceptionFactory->createRelationshipNotExistsException($name);
            }
        }
    }

    /**
     * @param string $dateTime
     */
    private function validateDateTime($dateTime): ConstraintViolationListInterface
    {
        return $this->validator->validate($dateTime, new DateTime());
    }

    /**
     * @param string $date
     */
    private function validateDate($date): ConstraintViolationListInterface
    {
        return $this->validator->validate($date, new Date());
    }

    private function getValidator(string $type): ?string
    {
        switch ($type) {
            case 'date':
            case 'date_immutable':
                return 'validateDate';

            case 'datetime':
            case 'datetime_immutable':
            case 'datetimetz':
            case 'datetimetz_immutable':
                return 'validateDateTime';
        }

        return null;
    }
}
