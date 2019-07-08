<?php
declare(strict_types=1);

namespace Symfony\Bundle\MakerBundle\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use Bornfight\JsonApiBundle\JsonApiStr;

class JsonApiStrTest extends TestCase
{
    /** @dataProvider provideEntityNameToType */
    public function testEntityNameToType($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, JsonApiStr::entityNameToType($value));
    }

    /** @dataProvider provideSingularizeClassName */
    public function testSingularizeClassName($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, JsonApiStr::singularizeClassName($value));
    }

    /** @dataProvider providePluralizeClassName */
    public function testPluralizeClassName($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, JsonApiStr::pluralizeClassName($value));
    }

    /** @dataProvider provideEenEntityIdName */
    public function testEenEntityIdName($value, $withBrackets, $expectedResult): void
    {
        $this->assertSame($expectedResult, JsonApiStr::genEntityIdName($value, $withBrackets));
    }

    public function provideEntityNameToType(): ?Generator
    {
        yield['Author', 'authors'];
        yield['Authors', 'authors'];
        yield['App\\Entity\\Authors', 'authors'];
        yield['YummyFood', 'yummy_foods'];
        yield['YummyFoods', 'yummy_foods'];
        yield['App\\Entity\\YummyFoods', 'yummy_foods'];
    }

    public function provideSingularizeClassName(): ?Generator
    {
        yield['Author', 'Author'];
        yield['Authors', 'Author'];
        yield['App\\Entity\\Authors', 'Author'];
        yield['YummyFood', 'YummyFood'];
        yield['YummyFoods', 'YummyFood'];
        yield['App\\Entity\\YummyFoods', 'YummyFood'];
    }

    public function providePluralizeClassName(): ?Generator
    {
        yield['Author', 'Authors'];
        yield['App\\Entity\\Author', 'Authors'];
        yield['Authors', 'Authors'];
        yield['YummyFood', 'YummyFoods'];
        yield['App\\Entity\\YummyFood', 'YummyFoods'];
        yield['YummyFoods', 'YummyFoods'];
    }

    public function provideEenEntityIdName(): ?Generator
    {
        yield['Author', false, 'Author_id'];
        yield['Authors', false, 'Author_id'];
        yield['Author', true, '{Author_id}'];
        yield['YummyFood', false, 'YummyFood_id'];
        yield['YummyFoods', false, 'YummyFood_id'];
        yield['YummyFood', true, '{YummyFood_id}'];
    }
}
