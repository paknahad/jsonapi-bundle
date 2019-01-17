<?php

namespace Paknahad\JsonApiBundle\Hydrator;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Entity;
use Paknahad\JsonApiBundle\Exception\InvalidAttributeException;
use Paknahad\JsonApiBundle\Exception\InvalidRelationshipValueException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validation;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidatorTrait
{
    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param ToOneRelationship|ToManyRelationship $relation
     * @param array                                $validTypes
     *
     * @throws \Exception | ValidatorException
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
     * @param array  $availableEntities
     * @param array  $requestedIds
     * @param string $relationshipName
     *
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
     * @param ClassMetadata    $metadata
     * @param RequestInterface $request
     *
     * @throws InvalidAttributeException
     */
    protected function validateFields(ClassMetadata $metadata, RequestInterface $request, bool $validExistance = true): void
    {
        $this->validator = Validation::createValidator();

        foreach ($request->getResourceAttributes() as $field => $value) {
            if ($validExistance && !$metadata->hasField($field)) {
                throw new ValidatorException('This attribute dose not exist');
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
     * @param string $dateTime
     *
     * @return ConstraintViolationListInterface
     */
    private function validateDateTime($dateTime): ConstraintViolationListInterface
    {
        return $this->validator->validate($dateTime, new DateTime());
    }

    /**
     * @param string $date
     *
     * @return ConstraintViolationListInterface
     */
    private function validateDate($date): ConstraintViolationListInterface
    {
        return $this->validator->validate($date, new Date());
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
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
