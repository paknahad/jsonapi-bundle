<?php

namespace Paknahad\JsonApiBundle\Helper\Filter\Filter;

/**
 * Class FinderHandler.
 */
class FinderHandler
{
    private $finders;

    public function __construct()
    {
        $this->finders = [];
    }

    /**
     * @param FinderInterface $finder
     */
    public function addFinder(FinderInterface $finder)
    {
        $this->finders[] = $finder;
    }

}
