<?php
namespace Paknahad\JsonApiBundle\Hydrator;

use Paknahad\JsonApiBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator as BaseHydrator;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractHydrator extends BaseHydrator
{
    protected $objectManager;
    protected $voter;
    protected $token;
    protected $action;

    public function __construct(string $action, ObjectManager $objectManager, TokenInterface $token, ?AbstractVoter $voter = null)
    {
        $this->objectManager = $objectManager;
        $this->voter = $voter;
        $this->token = $token;
        $this->action = $action;
    }

    protected function filterAttributes(array $fields, $subject): array
    {
        return $this->voter->voteOnInputFields($this->action, $subject, $this->token, $fields);
    }

    protected function filterRelations(array $relations, $subject): array
    {
        return $this->voter->voteOnInputRelations($this->action, $subject, $this->token, $relations);
    }
}
