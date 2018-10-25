<?= "<?php\n" ?>

namespace App\Security\Voter;

use <?= $entity_full_class_name ?>;
use Paknahad\JsonApiBundle\Security\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class <?= $entity_class_name ?>Voter extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['list', 'create', 'view', 'edit', 'delete']) && $subject instanceof <?= $entity_class_name ?>;
    }

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
                // logic to determine if the user can EDIT
                // return true or false
                return true;
            case 'create':
                // logic to determine if the user can CREATE
                // return true or false
                return true;
            case 'view':
                // logic to determine if the user can VIEW
                // return true or false
                return true;
            case 'edit':
                // logic to determine if the user can EDIT
                // return true or false
                return true;
            case 'delete':
                // logic to determine if the user can DELETE
                // return true or false
                return true;
        }

        return false;
    }

    protected function voteOnInputFields(string $attribute, $subject, TokenInterface $token, array $fields): array
    {
        $acceptableFields = [
<?php
foreach ($fields as $field) {
?>
            '<?= $field['name'] ?>',

<?php
}
?>
        ];

        // logic to define acceptable fields

        return array_intersect_key($fields, array_flip($acceptableFields));
    }

    protected function voteOnOutputFields(string $attribute, $subject, TokenInterface $token, array $fields): array
    {
        $acceptableFields = [
<?php
foreach ($fields as $field) {
?>
            '<?= $field['name'] ?>',

<?php
}
?>
        ];

        // logic to define acceptable fields

        return array_intersect_key($fields, array_flip($acceptableFields));
    }

    protected function voteOnInputRelations(string $attribute, $subject, TokenInterface $token, array $relations): array
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

    protected function voteOnOutputRelations(string $attribute, $subject, TokenInterface $token, array $relations): array
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
