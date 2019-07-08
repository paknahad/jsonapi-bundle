<?php
declare(strict_types=1);

namespace Bornfight\JsonApiBundle\Test;

use Bornfight\JsonApiBundle\Test\Constraint\IsValidJsonApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JsonApiTestCase extends WebTestCase
{
    public static function assertIsValidJsonApi($actual, string $message = ''): void
    {
        $constraint = new IsValidJsonApi();

        static::assertThat($actual, $constraint, $message);
    }
}
