<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class RepeatWithDataProviderAndDependsTest extends TestCase
{
    #[DataProvider('provide1')]
    public function test1(bool $bool): void
    {
        self::assertTrue($bool);
    }

    public static function provide1(): iterable
    {
        yield [true];
        yield [true];
    }

    #[Depends('test1')]
    public function testDependsOn1(): void
    {
        self::assertTrue(true);
    }

    #[DataProvider('provide2')]
    public function test2(bool $bool): void
    {
        self::assertTrue($bool);
    }

    public static function provide2(): iterable
    {
        yield [true];
        yield [false];
        yield [true];
    }

    #[Depends('test2')]
    public function testDependsOn2(): void
    {
        self::assertTrue(true);
    }
}
