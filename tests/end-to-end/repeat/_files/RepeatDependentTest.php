<?php

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class RepeatDependentTest extends TestCase
{
    public function test1(): void
    {
        self::assertTrue(true);
    }

    #[Depends('test1')]
    public function testDepends1(): void
    {
        self::assertTrue(true);
    }

    public function test2(): void
    {
        self::assertTrue(false);
    }

    #[Depends('test2')]
    public function testDepends2(): void
    {
        self::assertTrue(true);
    }
}
