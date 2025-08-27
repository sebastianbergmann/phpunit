<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder\Dependencies;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FooTest extends TestCase
{
    #[Depends('testTwo')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
