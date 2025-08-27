<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder\DifferentSizes;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversNothing]
final class UnitTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
