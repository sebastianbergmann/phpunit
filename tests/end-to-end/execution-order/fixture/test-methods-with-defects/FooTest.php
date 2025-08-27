<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder\Defects;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversNothing]
final class FooTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(false);
    }

    public function testThree(): void
    {
        throw new RuntimeException('message');
    }

    public function testFour(): void
    {
        $this->assertTrue(true);
    }
}
