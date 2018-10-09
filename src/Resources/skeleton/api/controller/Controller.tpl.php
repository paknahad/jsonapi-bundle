<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $document_full_class_name ?>;
use <?= $documents_full_class_name ?>;
use <?= $create_hydrator_full_class_name ?>;
use <?= $update_hydrator_full_class_name ?>;
use <?= $transformer_full_class_name ?>;
use <?= $repository_full_class_name ?>;
use Paknahad\JsonApiBundle\Controller\Controller;
use Paknahad\JsonApiBundle\Helper\Paginator;
use Paknahad\JsonApiBundle\Helper\ResourceCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Http\Message\ResponseInterface;

/**
 * @Route("<?= $route_path ?>")
 */
class <?= $class_name ?> extends Controller
{
    /**
     * @Route("/", name="<?= $route_name ?>_index", methods="GET")
     */
    public function index(Request $request, <?= $repository_class_name ?> $<?= $repository_var ?>): ResponseInterface
    {
        $query = $this->generateQuery($<?= $repository_var ?>, $request);

        $paginator = new Paginator($query, $request);

        return $this->jsonApi()->respond()->ok(
            new <?= $documents_class_name ?>(new <?= $transformer_class_name ?>()),
            new ResourceCollection(
                $paginator->getPagination(),
                $paginator->getCount(),
                $paginator->getPage(),
                $paginator->getSize()
            )
        );
    }

    /**
     * @Route("/", name="<?= $route_name ?>_new", methods="POST")
     */
    public function new(): ResponseInterface
    {
        $entityManager = $this->getDoctrine()->getManager();

        $<?= $entity_var_name ?> = $this->jsonApi()->hydrate(new <?= $create_hydrator_class_name ?>($entityManager), new <?= $entity_class_name ?>());

        /** @var ConstraintViolationList $errors */
        $errors = $this->get('validator')->validate($<?= $entity_var_name ?>);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $entityManager->persist($<?= $entity_var_name ?>);
        $entityManager->flush();

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $<?= $entity_var_name ?>

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
     */
    public function edit(<?= $entity_class_name ?> $<?= $entity_var_name ?>): ResponseInterface
    {
        $entityManager = $this->getDoctrine()->getManager();

        $<?= $entity_var_name ?> = $this->jsonApi()->hydrate(new <?= $update_hydrator_class_name ?>($entityManager), $<?= $entity_var_name ?>);

        /** @var ConstraintViolationList $errors */
        $errors = $this->get('validator')->validate($<?= $entity_var_name ?>);
        if ($errors->count() > 0) {
            return $this->validationErrorResponse($errors);
        }

        $entityManager->flush();

        return $this->jsonApi()->respond()->ok(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $<?= $entity_var_name ?>

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
