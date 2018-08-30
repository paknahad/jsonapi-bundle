<?php
namespace Paknahad\JsonApiBundle\Helper;

use IteratorAggregate;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PageBasedPaginationLinkProviderTrait;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PaginationLinkProviderInterface;

/**
 * Resource Collection
 *
 * @package Paknahad\Lib
 */
class ResourceCollection implements IteratorAggregate, PaginationLinkProviderInterface
{
    use PageBasedPaginationLinkProviderTrait;

    private $items;
    private $totalItems;
    private $page;
    private $size;

    public function __construct($items, int $totalItems, int $page, int $size)
    {
        $this->items = $items;
        $this->totalItems = $totalItems;
        $this->page = $page;
        $this->size = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->items;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
