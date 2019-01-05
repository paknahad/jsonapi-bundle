<?php

namespace Paknahad\JsonApiBundle\Test;

use Paknahad\JsonApiBundle\Test\Constraint\IsValidJsonApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JsonApiTestCase extends WebTestCase
{
    public static function assertIsValidJsonApi($actual, string $message = ''): void
    {
        $constraint = new IsValidJsonApi();

        static::assertThat($actual, $constraint, $message);
    }
}
