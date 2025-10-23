<?php

use PHPUnit\Framework\TestCase;

final class RepeatWithErrorsTest extends TestCase
{
    public function test1(): void
    {
        self::assertFalse(true);
    }

    public function test2(): void
    {
        static $cout = 0;

        if ($cout++ > 0) {
            self::assertFalse(true);
        }

        self::assertTrue(true);
    }

    public function test3(): void
    {
        static $cout = 0;

        if ($cout++ > 1) {
            self::assertFalse(true);
        }

        self::assertTrue(true);
    }

    public function test4(): void
    {
        self::assertTrue(true);
    }
}
