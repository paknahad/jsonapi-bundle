<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use Paknahad\JsonApiBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class <?= $entity_class_name ?>Voter extends AbstractVoter
{
    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['list', 'create', 'view', 'update', 'delete']) && $subject instanceof <?= $entity_class_name ?>;
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /*
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        */

        switch ($attribute) {
            case 'list':
            case 'view':
                // logic to determine if the user can VIEW
                // return true or false
                return true;
            case 'create':
                // logic to determine if the user can CREATE
                // return true or false
                return true;
            case 'update':
                // logic to determine if the user can UPDATE
                // return true or false
                return true;
            case 'delete':
                // logic to determine if the user can DELETE
                // return true or false
                return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function voteOnInputFields(string $attribute, $subject, TokenInterface $token, array $fields): array
    {
        $acceptableFields = [
<?php
foreach ($fields as $field) {
    if (isset($field['id']) && $field['id']) {
        continue;
    }
?>
            '<?= $field['name'] ?>',
<?php
}
?>
        ];

        // logic to define acceptable fields

        return array_intersect_key($fields, array_flip($acceptableFields));
    }

    /**
     * @inheritdoc
     */
    public function voteOnOutputFields($subject, TokenInterface $token, array $fields): array
    {
        $acceptableFields = [
<?php
foreach ($fields as $field) {
    if (isset($field['id']) && $field['id']) {
        continue;
    }
?>
            '<?= $field['name'] ?>',
<?php
}
?>
        ];

        // logic to define acceptable fields

        return array_intersect_key($fields, array_flip($acceptableFields));
    }

    /**
     * @inheritdoc
     */
    public function voteOnInputRelations(string $attribute, $subject, TokenInterface $token, array $relations): array
    {
        $acceptableRelations = [
<?php
foreach ($associations as $association) {
?>
            '<?= $association['field_name'] ?>',
<?php
}
?>
        ];

        // logic to define acceptable relations

        return array_intersect_key($relations, array_flip($acceptableRelations));
    }

    /**
     * @inheritdoc
     */
    public function voteOnOutputRelations($subject, TokenInterface $token, array $relations): array
    {
        $acceptableRelations = [
<?php
foreach ($associations as $association) {
?>
            '<?= $association['field_name'] ?>',
<?php
}
?>
        ];

        // logic to define acceptable relations

        return array_intersect_key($relations, array_flip($acceptableRelations));
    }
}
