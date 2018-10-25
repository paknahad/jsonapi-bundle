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
    abstract protected function voteOnInputFields(string $attribute, $subject, TokenInterface $token, array $fields): array;

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $fields
     *
     * @return array
     */
    abstract protected function voteOnOutputFields(string $attribute, $subject, TokenInterface $token, array $fields): array;

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $relations
     *
     * @return array
     */
    abstract protected function voteOnInputRelations(string $attribute, $subject, TokenInterface $token, array $relations): array;

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     * @param array          $relations
     *
     * @return array
     */
    abstract protected function voteOnOutputRelations(string $attribute, $subject, TokenInterface $token, array $relations): array;
}
