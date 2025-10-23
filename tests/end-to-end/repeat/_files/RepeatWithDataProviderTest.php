<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RepeatWithDataProviderTest extends TestCase
{
    #[DataProvider('provide')]
    public function test1(bool $bool): void
    {
        self::assertTrue($bool);
    }

    public static function provide(): iterable
    {
        yield [true];
        yield [true];
    }
}
