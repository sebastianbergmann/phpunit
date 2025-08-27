<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FooTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
