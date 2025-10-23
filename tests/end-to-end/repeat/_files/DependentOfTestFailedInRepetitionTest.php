<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DependentOfTestFailedInRepetitionTest extends TestCase
{
    public function test1(): void
    {
        static $cout = 0;

        if ($cout++ > 0) {
            self::assertFalse(true);
        }

        self::assertTrue(true);
    }

    #[Depends('test1')]
    public function test2(): void
    {
        self::assertTrue(true);
    }
}
