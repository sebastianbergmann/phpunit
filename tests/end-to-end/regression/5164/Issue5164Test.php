<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class Issue5164Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped('message');
    }

    public function testOne(): void
    {
    }

    public function testTwo(): void
    {
    }
}
