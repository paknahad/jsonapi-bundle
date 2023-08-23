<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $document_full_class_name ?>;
use <?= $documents_full_class_name ?>;
use <?= $create_hydrator_full_class_name ?>;
use <?= $update_hydrator_full_class_name ?>;
use <?= $transformer_full_class_name ?>;
use <?= $repository_full_class_name ?>;
use Devleand\JsonApiBundle\Controller\Controller;
use Devleand\JsonApiBundle\Helper\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("<?= $route_path ?>")
 */
class <?= $class_name ?> extends Controller
{
    /**
     * @Route("/", name="<?= $route_name ?>_index", methods="GET")
     */
    public function index(<?= $repository_class_name ?> $<?= $repository_var ?>, ResourceCollection $resourceCollection): Response
    {
        $resourceCollection->setRepository($<?= $repository_var ?>);

        $resourceCollection->handleIndexRequest();

        return $this->respondOk(
            new <?= $documents_class_name ?>(new <?= $transformer_class_name ?>()),
            $resourceCollection
        );
    }

    /**
     * @Route("/", name="<?= $route_name ?>_new", methods="POST")
     */
    public function new(): Response
    {
        $<?= $entity_var_name ?> = $this->jsonApi()->hydrate(
            new <?= $create_hydrator_class_name ?>($this->entityManager, $this->jsonApi()->getExceptionFactory()),
            new <?= $entity_class_name ?>()
        );

        $this->validate($<?= $entity_var_name ?>);

        $this->entityManager->persist($<?= $entity_var_name ?>);
        $this->entityManager->flush();

        return $this->respondOk(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_show", methods="GET")
     */
    public function show(<?= $entity_class_name ?> $<?= $entity_var_name ?>): Response
    {
        return $this->respondOk(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_edit", methods="PATCH")
     */
    public function edit(<?= $entity_class_name ?> $<?= $entity_var_name ?>): Response
    {
        $<?= $entity_var_name ?> = $this->jsonApi()->hydrate(
            new <?= $update_hydrator_class_name ?>($this->entityManager, $this->jsonApi()->getExceptionFactory()),
            $<?= $entity_var_name ?>

        );

        $this->validate($<?= $entity_var_name ?>);

        $this->entityManager->flush();

        return $this->respondOk(
            new <?= $document_class_name ?>(new <?= $transformer_class_name ?>()),
            $<?= $entity_var_name ?>

        );
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods="DELETE")
     */
    public function delete(<?= $entity_class_name ?> $<?= $entity_var_name ?>): Response
    {
        $this->entityManager->remove($<?= $entity_var_name?>);
        $this->entityManager->flush();

        return $this->respondNoContent();
    }
}
