<?php

namespace Paknahad\JsonApiBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Paknahad\JsonApiBundle\Lib\Finder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as Base;
use WoohooLabs\Yin\JsonApi\JsonApi;

class Controller extends Base
{
    private static $jsonApi;

    /**
     * @return JsonApi
     */
    protected function jsonApi()
    {
        if (! self::$jsonApi) {
            self::$jsonApi = $this->container->get('request_stack')->getCurrentRequest()->get('JsonApi');
        }

        return self::$jsonApi;
    }

    /**
     * Creates a QueryBuilder by EntityRepository and applies requested filters on that
     *
     * @param ServiceEntityRepository $repository
     * @param array                   $filters
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    protected function generateQuery(ServiceEntityRepository $repository, array $filters): QueryBuilder
    {
        $finder = new Finder($repository, $filters);

        return $finder->getFilteredQuery();
    }
}