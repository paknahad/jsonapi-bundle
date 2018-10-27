<?php
namespace Paknahad\JsonApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter
{
    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $fields
     *
     * @return array
     */
    abstract public function voteOnInputFields(string $attribute, $subject, TokenInterface $token, array $fields): array;

    /**
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $fields
     *
     * @return array
     */
    abstract public function voteOnOutputFields($subject, TokenInterface $token, array $fields): array;

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $relations
     *
     * @return array
     */
    abstract public function voteOnInputRelations(string $attribute, $subject, TokenInterface $token, array $relations): array;

    /**
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $relations
     *
     * @return array
     */
    abstract public function voteOnOutputRelations($subject, TokenInterface $token, array $relations): array;
}
