<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $document_full_class_name ?>;
use <?= $documents_full_class_name ?>;
use <?= $hydrator_full_class_name ?>;
use <?= $transformer_full_class_name ?>;
use <?= $repository_full_class_name ?>;
use Paknahad\JsonApiBundle\Controller\Controller;
use Paknahad\JsonApiBundle\Helper\ResourceCollection;
use Paknahad\JsonApiBundle\Helper\InputOutputManager;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Http\Message\ResponseInterface;

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

        $this->denyAccessUnlessGranted('list', new <?= $entity_class_name ?>());

        return $this->jsonApi()->respond()->ok(
            new <?= $documents_class_name ?>(InputOutputManager::makeTransformer(<?= $transformer_class_name ?>::class)),
            $resourceCollection
        );
    }

    /**
     * @Route("/", name="<?= $route_name ?>_new", methods="POST")
     */
    public function new(ValidatorInterface $validator): ResponseInterface
    {
        $entityManager = $this->getDoctrine()->getManager();

        $<?= $entity_var_name ?> = $this->jsonApi()->hydrate($this->inputOutputManager->makeHydrator('create', <?= $hydrator_class_name ?>::class), new <?= $entity_class_name ?>());

        $this->denyAccessUnlessGranted('create', $<?= $entity_var_name ?>);

        /** @var ConstraintViolationList $errors */
        $errors = $validator->validate($<?= $entity_var_name ?>);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $entityManager->persist($<?= $entity_var_name ?>);
        $entityManager->flush();

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(InputOutputManager::makeTransformer(<?= $transformer_class_name ?>::class)),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_show", methods="GET")
     */
    public function show(<?= $entity_class_name ?> $<?= $entity_var_name ?>): ResponseInterface
    {
        $this->denyAccessUnlessGranted('view', $<?= $entity_var_name ?>);

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(InputOutputManager::makeTransformer(<?= $transformer_class_name ?>::class)),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_edit", methods="PATCH")
     */
    public function edit(<?= $entity_class_name ?> $<?= $entity_var_name ?>, ValidatorInterface $validator): ResponseInterface
    {
        $entityManager = $this->getDoctrine()->getManager();

        $<?= $entity_var_name ?> = $this->jsonApi()->hydrate($this->inputOutputManager->makeHydrator('update', <?= $hydrator_class_name ?>::class), $<?= $entity_var_name ?>);

        $this->denyAccessUnlessGranted('update', $<?= $entity_var_name ?>);

        /** @var ConstraintViolationList $errors */
        $errors = $validator->validate($<?= $entity_var_name ?>);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $entityManager->flush();

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(InputOutputManager::makeTransformer(<?= $transformer_class_name ?>::class)),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods="DELETE")
     */
    public function delete(<?= $entity_class_name ?> $<?= $entity_var_name ?>): ResponseInterface
    {
        $this->denyAccessUnlessGranted('delete', $<?= $entity_var_name ?>);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($<?= $entity_var_name?>);
        $entityManager->flush();

        return $this->jsonApi()->respond()->genericSuccess(204);
    }
}
