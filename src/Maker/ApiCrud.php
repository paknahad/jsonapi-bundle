<?php

namespace Paknahad\JsonApiBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Paknahad\JsonApiBundle\Collection\PostmanCollectionGenerator;
use Paknahad\JsonApiBundle\Collection\SwaggerCollectionGenerator;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser\Php7;
use PhpParser\Lexer;
use ReflectionClass;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassDetails;
use Symfony\Bundle\MakerBundle\Util\ClassSourceManipulator;
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
    private $fileManager;
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(PostmanCollectionGenerator $postmanGenerator, SwaggerCollectionGenerator $swaggerGenerator, DoctrineHelper $doctrineHelper, FileManager $fileManager, NodeFactory $nodeFactory)
    {
        $this->postmanGenerator = $postmanGenerator;
        $this->swaggerGenerator = $swaggerGenerator;
        $this->doctrineHelper = $doctrineHelper;
        $this->fileManager = $fileManager;
        $this->nodeFactory = $nodeFactory;
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
            ->setHelp(file_get_contents(__DIR__ . '/../Resources/help/MakeCrud.txt'));

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
                '\\' . $entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );

            $repositoryVars = [
                'repository_full_class_name' => $repositoryClassDetails->getFullName(),
                'repository_class_name' => $repositoryClassDetails->getShortName(),
                'repository_var' => lcfirst(Inflector::singularize($repositoryClassDetails->getShortName())),
            ];
        }

        $entityVarPlural = Inflector::pluralize($entityClassDetails->getShortName());
        $entityVarSingular = Inflector::singularize($entityClassDetails->getShortName());

        $controllerClassDetails = $generator->createClassNameDetails(
            $entityVarSingular,
            'Controller\\Api\\', // TODO add config dir
            'Controller'
        );

        $documentClassDetails = $generator->createClassNameDetails(
            $entityVarSingular,
            'JsonApi\\Document\\' . $entityClassDetails->getShortName(),
            'Document'
        );
        $documentsClassDetails = $generator->createClassNameDetails(
            $entityVarPlural,
            'JsonApi\\Document\\' . $entityClassDetails->getShortName(),
            'Document'
        );
        $transformerClassDetails = $generator->createClassNameDetails(
            $entityVarSingular,
            'JsonApi\\Transformer\\',
            'ResourceTransformer'
        );

        foreach (['abstract', 'create', 'update'] as $key) {
            $hydratorClassDetails[$key] = $generator->createClassNameDetails(
                ucfirst($key) . $entityVarSingular,
                sprintf('JsonApi\\Hydrator\\%s', $entityClassDetails->getShortName()),
                'Hydrator'
            );
        }

        $entityTypeVarPlural = Str::asTwigVariable($entityVarPlural);

        $routeName = Str::asRouteName($entityVarPlural);
        $routePath = Str::asRoutePath($entityVarPlural);
        $skeletonPath = __DIR__ . '/../Resources/skeleton/';


        $classExists = class_exists($controllerClassDetails->getFullName());
        if (!$classExists) {

            $generator->generateClass(
                $controllerClassDetails->getFullName(),
                $skeletonPath . 'api/controller/Controller.tpl.php',
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
        } else {
            $this->writeWarning('Controller class already exists, skipping..', $io);
        }

        $classExists = class_exists($documentClassDetails->getFullName());
        if (!$classExists) {
            $generator->generateClass(
                $documentClassDetails->getFullName(),
                $skeletonPath . 'api/JsonApi/Document/EntityDocument.tpl.php',
                [
                    'route_path' => $routePath,
                    'entity_class_name' => $entityClassDetails->getShortName(),
                    'namespace' => $documentClassDetails->getFullName(),
                ]
            );

        } else {
            $this->writeWarning('Document class already exists, skipping..', $io);
        }
        $classExists = class_exists($documentsClassDetails->getFullName());
        if (!$classExists) {
            $generator->generateClass(
                $documentsClassDetails->getFullName(),
                $skeletonPath . 'api/JsonApi/Document/EntitiesDocument.tpl.php',
                [
                    'route_path' => $routePath,
                    'entity_class_name' => $entityClassDetails->getShortName(),
                    'entity_class_name_plural' => $entityVarPlural,
                    'namespace' => $documentsClassDetails->getFullName(),
                ]
            );
        } else {
            $this->writeWarning('Documents class already exists, skipping..', $io);
        }
        $toMayTypes = [
            ClassMetadataInfo::TO_MANY,
            ClassMetadataInfo::MANY_TO_MANY,
            ClassMetadataInfo::ONE_TO_MANY,
        ];

        $classExists = class_exists($transformerClassDetails->getFullName());

        if (!$classExists) {
            $generator->generateClass(
                $transformerClassDetails->getFullName(),
                $skeletonPath . 'api/JsonApi/Transformer/EntityResourceTransformer.tpl.php',
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

        } else {
            $transformerPath = $this->getPathOfClass($transformerClassDetails->getFullName());
            $manipulator = new ClassSourceManipulator($this->fileManager->getFileContents($transformerPath), true);
            $manipulator->setIo($io);

            $propertyNames = $this->getPropertyNames($entityClassDetails->getFullName());
            $propertyNames = array_diff($propertyNames, ['id']);


            $traverser = new NodeTraverser;
            $traverser->addVisitor(new class($propertyNames, $entityClassDetails->getShortName()) extends NodeVisitorAbstract {

                private $className;

                public function __construct($propertyNames, $className) {
                    $this->propertyNames = $propertyNames;
                    $this->className = $className;
                }

                public function leaveNode(Node $node)
                {
                    if ($node instanceof Node\Stmt\ClassMethod and $node->name->name === 'getAttributes') {
                        foreach ($node->stmts as $stmt) {
                            if ($stmt instanceof Node\Stmt\Return_) {
                                $existingItems = $stmt->expr->items;
                                $existingItemNames = $stmt->expr->items;
                                $newItems = [];

                                foreach ($existingItems as $existingItem) {
                                    $existingItemNames[] = $existingItem->key->value;
                                }

                                foreach ($this->propertyNames as $propertyName) {

                                    if(!in_array($propertyName, $existingItemNames)){
                                        $newItems[] = $propertyName;
                                    }
                                }
                                foreach ($newItems as $newItem){
                                    $key = new Node\Scalar\String_($newItem);
                                    $type = new Node\Name($this->className);
                                    $var = new Node\Expr\Variable(lcfirst($this->className));
                                    $closureParam = new Node\Param($var,null,$type);
                                    $functionName = 'get'.ucfirst($newItem);
                                    $expr = new Node\Expr\MethodCall($var,$functionName);
                                    $return = new Node\Stmt\Return_($expr);

                                    $subNodes = [];
                                    $subNodes['params'] = [$closureParam];
                                    $subNodes['stmts'] = [$return];

                                    $closure = new Node\Expr\Closure($subNodes);
                                    $newAttribute = new Node\Expr\ArrayItem($closure,$key);
                                    $existingItems[] = $newAttribute;
                                }
                                $stmt->expr->items = $existingItems;


                            }
                        }
                    }
                }
            });
            $lexer = new Lexer\Emulative([
                'usedAttributes' => [
                    'comments',
                    'startLine', 'endLine',
                    'startTokenPos', 'endTokenPos',
                ],
            ]);
            $parser = new Php7($lexer);
            $stmts = $parser->parse($this->fileManager->getFileContents($transformerPath));
            $modifiedStmts = $traverser->traverse($stmts);

            //TODO functions
            $prettyPrinter = new PrettyPrinter\Standard();

            $this->fileManager->dumpFile($transformerPath, $prettyPrinter->prettyPrintFile($stmts));

        }
        foreach (['abstract', 'create', 'update'] as $key) {
            $generator->generateClass(
                $hydratorClassDetails[$key]->getFullName(),
                $skeletonPath . sprintf('api/JsonApi/Hydrator/%sEntityHydrator.tpl.php', ucfirst($key)),
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

        $this->postmanGenerator->generateCollection($entityMetadata, $entityClassDetails->getShortName(), $routePath);

        $this->swaggerGenerator->generateCollection($entityMetadata, $entityClassDetails->getShortName(), $routePath);

        $this->writeSuccessMessage($io);

        $io->text(
            sprintf(
                'Next: Use Postman_Collection.json to test your API. You can find that in <fg=yellow>%s</>',
                PostmanCollectionGenerator::POSTMAN_PATH
            )
        );
    }


    private function getPropertyNames(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflClass = new ReflectionClass($class);

        return array_map(function (\ReflectionProperty $prop) {
            return $prop->getName();
        }, $reflClass->getProperties());
    }

    private function getPathOfClass(string $class): string
    {
        $classDetails = new ClassDetails($class);

        return $classDetails->getPath();
    }

    private function writeWarning(string $message, ConsoleStyle $io)
    {

        $io->writeln('<fg=yellow;options=bold,underscore>[Warning] ' . $message . '</>');
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
                'field_name_singular' => Inflector::singularize($association['fieldName']),
                'type' => $association['type'],
                'target_entity' => $association['targetEntity'],
                'target_entity_name' => $entityName,
                'target_entity_type' => Str::asTwigVariable(Inflector::pluralize($entityName)),
                'getter' => 'get' . Str::asCamelCase($association['fieldName']),
                'setter' => 'set' . Str::asCamelCase($association['fieldName']),
                'adder' => 'add' . Str::asCamelCase(Inflector::singularize($association['fieldName'])),
                'remover' => 'remove' . Str::asCamelCase(Inflector::singularize($association['fieldName'])),
            ];
        }

        return $associations;
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
                'getter' => 'get' . Str::asCamelCase($field['fieldName']),
                'setter' => 'set' . Str::asCamelCase($field['fieldName']),
            ];
        }

        return $fields;
    }
}
