<?php

namespace Paknahad\JsonApiBundle\Helper\Filter;

interface SubQueryManagerInterface
{
    /**
     * Checking this subQuery exists or not.
     *
     * @param string $entityClass
     * @param string $subQuery
     *
     * @return bool
     */
    public function exists(string $entityClass, string $subQuery): bool;

    /**
     * Generate MetaData for subQuery almost same as fields.
     *
     * @param string $entityClass
     * @param string $subQuery
     *
     * @return array
     */
    public function generateMetaData(string $entityClass, string $subQuery): array;

    /**
     * Return sunQuery's DQL.
     *
     * @param string $subQuery
     *
     * @return string
     */
    public function getSubQuery(string $subQuery): string;
}
