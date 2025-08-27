<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder\DifferentSizes;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;

#[Medium]
#[CoversNothing]
final class IntegrationTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
