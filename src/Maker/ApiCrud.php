<?php

namespace Devleand\JsonApiBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Inflector\LanguageInflectorFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Devleand\JsonApiBundle\Collection\OpenApiCollectionGenerator;
use Devleand\JsonApiBundle\Collection\PostmanCollectionGenerator;
use Devleand\JsonApiBundle\Collection\SwaggerCollectionGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;

/**
 * @author Hamid Paknahad <hp.paknahad@gmail.com>
 */
final class ApiCrud extends AbstractMaker
{
    private $postmanGenerator;
    private $swaggerGenerator;
    private $doctrineHelper;
    /**
     * @var OpenApiCollectionGenerator
     */
    private $openApiCollectionGenerator;
    /**
     * @var string
     */
    private $documentationSchema;

    /**
     * @var \Doctrine\Inflector\Inflector
     */
    private $inflector;
    /**
     * @var string
     */
    private $controllerNamespace;

    public function __construct(
        string $documentationSchema,
        string $controllerNamespace,
        PostmanCollectionGenerator $postmanGenerator,
        SwaggerCollectionGenerator $swaggerGenerator,
        OpenApiCollectionGenerator $openApiCollectionGenerator,
        DoctrineHelper $doctrineHelper,
        LanguageInflectorFactory $languageInflectorFactory
    ) {
        $this->postmanGenerator = $postmanGenerator;
        $this->swaggerGenerator = $swaggerGenerator;

        $this->doctrineHelper = $doctrineHelper;
        $this->openApiCollectionGenerator = $openApiCollectionGenerator;
        $this->documentationSchema = $documentationSchema;

        $this->inflector = $languageInflectorFactory->build();
        $this->controllerNamespace = $controllerNamespace;
    }

    public static function getCommandName(): string
    {
        return 'make:api';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates CRUD API for Doctrine entity class')
            ->addArgument(
                'entity-class',
                InputArgument::OPTIONAL,
                sprintf('The class name of the entity to create API (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm()))
            )
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeCrud.txt'))
        ;

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');

            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $value = $io->askQuestion($question);

            $input->setArgument('entity-class', $value);
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );

        $entityMetadata = $this->doctrineHelper->getMetadata($entityClassDetails->getFullName());
        $fields = $this->getFields($entityMetadata->fieldMappings);
        $associations = $this->getAssociations($entityMetadata->associationMappings);
        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());

        $repositoryVars = [];

        if ($entityMetadata) {
            $entityDoctrineDetails = new EntityDetails($entityMetadata);
            $repositoryClassDetails = $generator->createClassNameDetails(
                '\\'.$entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );

            $repositoryVars = [
                'repository_full_class_name' => $repositoryClassDetails->getFullName(),
                'repository_class_name' => $repositoryClassDetails->getShortName(),
                'repository_var' => lcfirst($this->inflector->singularize($repositoryClassDetails->getShortName())),
            ];
        }

        $entityVarPlural = $this->inflector->pluralize($entityClassDetails->getShortName());
        $entityVarSingular = $this->inflector->singularize($entityClassDetails->getShortName());

        $controllerClassDetails = $generator->createClassNameDetails(
            $entityVarSingular,
            $this->controllerNamespace.'\\',
            'Controller'
        );

        $documentClassDetails = $generator->createClassNameDetails(
            $entityVarSingular,
            'JsonApi\\Document\\'.$entityClassDetails->getShortName(),
            'Document'
        );
        $documentsClassDetails = $generator->createClassNameDetails(
            $entityVarPlural,
            'JsonApi\\Document\\'.$entityClassDetails->getShortName(),
            'Document'
        );
        $transformerClassDetails = $generator->createClassNameDetails(
            $entityVarSingular,
            'JsonApi\\Transformer\\',
            'ResourceTransformer'
        );

        foreach (['abstract', 'create', 'update'] as $key) {
            $hydratorClassDetails[$key] = $generator->createClassNameDetails(
                ucfirst($key).$entityVarSingular,
                sprintf('JsonApi\\Hydrator\\%s', $entityClassDetails->getShortName()),
                'Hydrator'
            );
        }

        $entityTypeVarPlural = Str::asTwigVariable($entityVarPlural);

        $routeName = Str::asRouteName($entityVarPlural);
        $routePath = Str::asRoutePath($entityVarPlural);
        $skeletonPath = __DIR__.'/../Resources/skeleton/';

        $generator->generateClass(
            $controllerClassDetails->getFullName(),
            $skeletonPath.'api/controller/Controller.tpl.php',
            array_merge(
                [
                    'entity_full_class_name' => $entityClassDetails->getFullName(),
                    'entity_class_name' => $entityClassDetails->getShortName(),
                    'route_path' => $routePath,
                    'route_name' => $routeName,
                    'entity_var_plural' => $entityVarPlural,
                    'entity_type_var_plural' => $entityTypeVarPlural,
                    'entity_var_singular' => $entityVarSingular,
                    'entity_var_name' => lcfirst($entityVarSingular),
                    'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                    'document_full_class_name' => $documentClassDetails->getFullname(),
                    'document_class_name' => $documentClassDetails->getShortName(),
                    'documents_full_class_name' => $documentsClassDetails->getFullname(),
                    'documents_class_name' => $documentsClassDetails->getShortName(),
                    'create_hydrator_full_class_name' => $hydratorClassDetails['create']->getFullname(),
                    'create_hydrator_class_name' => $hydratorClassDetails['create']->getShortName(),
                    'update_hydrator_full_class_name' => $hydratorClassDetails['update']->getFullname(),
                    'update_hydrator_class_name' => $hydratorClassDetails['update']->getShortName(),
                    'transformer_full_class_name' => $transformerClassDetails->getFullname(),
                    'transformer_class_name' => $transformerClassDetails->getShortName(),
                ],
                $repositoryVars
            )
        );

        $generator->generateClass(
            $documentClassDetails->getFullName(),
            $skeletonPath.'api/JsonApi/Document/EntityDocument.tpl.php',
            [
                'route_path' => $routePath,
                'entity_class_name' => $entityClassDetails->getShortName(),
                'namespace' => $documentClassDetails->getFullName(),
            ]
        );

        $generator->generateClass(
            $documentsClassDetails->getFullName(),
            $skeletonPath.'api/JsonApi/Document/EntitiesDocument.tpl.php',
            [
                'route_path' => $routePath,
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_class_name_plural' => $entityVarPlural,
                'namespace' => $documentsClassDetails->getFullName(),
            ]
        );

        $toMayTypes = [
            ClassMetadataInfo::TO_MANY,
            ClassMetadataInfo::MANY_TO_MANY,
            ClassMetadataInfo::ONE_TO_MANY,
        ];
        $generator->generateClass(
            $transformerClassDetails->getFullName(),
            $skeletonPath.'api/JsonApi/Transformer/EntityResourceTransformer.tpl.php',
            [
                'route_path' => $routePath,
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_var_name' => lcfirst($entityVarSingular),
                'entity_type_var_plural' => $entityTypeVarPlural,
                'namespace' => $transformerClassDetails->getFullName(),
                'fields' => $fields,
                'associations' => $associations,
                'to_many_types' => $toMayTypes,
            ]
        );

        foreach (['abstract', 'create', 'update'] as $key) {
            $generator->generateClass(
                $hydratorClassDetails[$key]->getFullName(),
                $skeletonPath.sprintf('api/JsonApi/Hydrator/%sEntityHydrator.tpl.php', ucfirst($key)),
                [
                    'route_path' => $routePath,
                    'entity_class_name' => $entityClassDetails->getShortName(),
                    'entity_var_name' => lcfirst($entityVarSingular),
                    'entity_full_class_name' => $entityClassDetails->getFullName(),
                    'entity_type_var_plural' => $entityTypeVarPlural,
                    'namespace' => $hydratorClassDetails[$key]->getFullName(),
                    'fields' => $fields,
                    'associations' => $associations,
                    'to_many_types' => $toMayTypes,
                ]
            );
        }

        $generator->writeChanges();

        $this->generateDocumentation($entityMetadata, $entityClassDetails->getShortName(), $routePath);

        $this->writeSuccessMessage($io);

        $io->text(
            sprintf(
                'Next: Use Postman_Collection.json to test your API. You can find that in <fg=yellow>%s</>',
                PostmanCollectionGenerator::POSTMAN_PATH
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'annotations'
        );

        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );
    }

    private function getAssociations(array $associationMappings): array
    {
        $associations = [];

        foreach ($associationMappings as $association) {
            $entityName = Str::getShortClassName($association['targetEntity']);

            $associations[] = [
                'field_name' => $association['fieldName'],
                'field_name_singular' => $this->inflector->singularize($association['fieldName']),
                'type' => $association['type'],
                'target_entity' => $association['targetEntity'],
                'target_entity_name' => $entityName,
                'target_entity_type' => Str::asTwigVariable($this->inflector->pluralize($entityName)),
                'getter' => 'get'.Str::asCamelCase($association['fieldName']),
                'setter' => 'set'.Str::asCamelCase($association['fieldName']),
                'adder' => 'add'.Str::asCamelCase($this->inflector->singularize($association['fieldName'])),
                'remover' => 'remove'.Str::asCamelCase($this->inflector->singularize($association['fieldName'])),
            ];
        }

        return $associations;
    }

    private function generateDocumentation(ClassMetadata $entityMetadata, string $getShortName, string $routePath): void
    {
        switch ($this->documentationSchema) {
            case 'swagger': // TODO make constants
                $this->swaggerGenerator->generateCollection($entityMetadata, $getShortName, $routePath);
                break;
            case 'openapi':  // TODO make constants
                $this->openApiCollectionGenerator->generateCollection($entityMetadata, $getShortName, $routePath);
                break;
        }

        $this->postmanGenerator->generateCollection($entityMetadata, $getShortName, $routePath);
    }

    private function getFields(array $fieldMappings): array
    {
        $fields = [];

        foreach ($fieldMappings as $key => $field) {
            $fields[$key] = [
                'id' => isset($field['id']) && $field['id'],
                'name' => $field['fieldName'],
                'type' => $field['type'],
                'unique' => $field['unique'] ?? false,
                'nullable' => $field['nullable'] ?? false,
                'getter' => 'get'.Str::asCamelCase($field['fieldName']),
                'setter' => 'set'.Str::asCamelCase($field['fieldName']),
            ];
        }

        return $fields;
    }
}
