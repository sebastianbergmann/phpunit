<?php

namespace unit\Framework\Attributes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertEmpty([]);
    }

    #[Depends('testOne')]
    #[DataProvider('dataProvider')]
    public function testTwo(string $example): void
    {
        $this->assertEmpty($example);
    }

    public static function dataProvider(): array
    {
        return [
            'case 1' => [
                'example' => ''
            ]
        ];
    }
}
