<?= "<?php\ndeclare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $document_full_class_name ?>;
use <?= $documents_full_class_name ?>;
use <?= $create_dto_full_class_name ?>;
use <?= $update_dto_full_class_name ?>;
use <?= $transformer_full_class_name ?>;
use <?= $repository_full_class_name ?>;
use Paknahad\JsonApiBundle\Controller\Controller;
use Paknahad\JsonApiBundle\Helper\ResourceCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("<?= $route_path ?>")
 */
class <?= $class_name ?> extends Controller
{
    /**
     * @Route("/", name="<?= $route_name ?>_index", methods="GET")
     */
    public function index(<?= $repository_class_name ?> $<?= $repository_var ?>, ResourceCollection $resourceCollection): ResponseInterface
    {
        $resourceCollection->setRepository($<?= $repository_var ?>);

        $resourceCollection->handleIndexRequest();

        return $this->jsonApi()->respond()->ok(
            new <?= $documents_class_name ?>(new <?= $transformer_class_name ?>()),
            $resourceCollection
        );
    }


    /**
     * @Route("/", name="<?= $route_name ?>_new", methods="POST")
     * @ParamConverter("<?= $create_dto_var_name ?>", converter="JsonApiParamConverter")
     */
    public function new(
        <?= $create_dto_class_name ?> $<?= $create_dto_var_name ?>,
        ConstraintViolationList $validationErrors
    ): ResponseInterface {
        if (count($validationErrors) > 0) {
            return $this->validationErrorResponse($validationErrors);
        }

        $dataContainer = $this-><?= $entity_var_name ?>Service->create<?= $entity_var_name ?>FromDTO($<?= $create_dto_var_name ?>);
        if ($dataContainer->hasViolations() === true) {
            return $this->validationErrorResponse($dataContainer->getViolations());
        }

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $dataContainer->getModel()

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_show", methods="GET")
     */
    public function show(<?= $entity_class_name ?> $<?= $entity_var_name ?>): ResponseInterface
    {
        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_edit", methods="PATCH")
     * @ParamConverter("<?= $update_dto_var_name ?>", converter="JsonApiParamConverter")
     */
    public function edit(
        <?= $entity_class_name ?> $<?= $entity_var_name ?>,
        <?= $update_dto_class_name ?> $<?= $update_dto_var_name ?>,
        ConstraintViolationList $validationErrors
    ): ResponseInterface {
        if (count($validationErrors) > 0) {
            return $this->validationErrorResponse($validationErrors);
        }

        $dataContainer = $this-><?= $entity_var_name ?>Service->update<?= $entity_var_name ?>FromDTO($<?= $entity_var_name ?>, $<?= $update_dto_var_name ?>);
        if ($dataContainer->hasViolations() === true) {
            return $this->validationErrorResponse($dataContainer->getViolations());
        }

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $dataContainer->getModel()
        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods="DELETE")
     */
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_name ?>): ResponseInterface
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($<?= $entity_var_name?>);
        $entityManager->flush();

        return $this->jsonApi()->respond()->genericSuccess(204);
    }
}
