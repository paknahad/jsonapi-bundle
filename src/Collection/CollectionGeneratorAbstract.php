<?php

namespace Devleand\JsonApiBundle\Collection;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bundle\MakerBundle\FileManager;

abstract class CollectionGeneratorAbstract
{
    protected $fileManager;
    protected $rootDirectory;

    public const LIST_ACTION = 'list';
    public const ADD_ACTION = 'add';
    public const EDIT_ACTION = 'edit';
    public const DELETE_ACTION = 'delete';
    public const VIEW_ACTION = 'view';

    private static $actions = [
        self::LIST_ACTION => ['title' => '%s List', 'method' => 'GET'],
        self::ADD_ACTION => ['title' => 'Add %s', 'method' => 'POST'],
        self::EDIT_ACTION => ['title' => 'Edit %s', 'method' => 'PATCH'],
        self::DELETE_ACTION => ['title' => 'Delete %s', 'method' => 'DELETE'],
        self::VIEW_ACTION => ['title' => 'Get %s', 'method' => 'GET'],
    ];

    public function __construct(FileManager $fileManager, string $rootDirectory)
    {
        $this->fileManager = $fileManager;
        $this->rootDirectory = $rootDirectory;
    }

    abstract public function generateCollection(ClassMetadataInfo $classMetadata, string $entityName, string $route): ?string;

    protected function getActionsList(string $entityName): array
    {
        $actions = self::$actions;
        foreach ($actions as $name => &$action) {
            $action['title'] = sprintf($action['title'], $entityName);
        }

        return $actions;
    }

    public function setSeed(int $int)
    {
        srand($int);
    }
}
