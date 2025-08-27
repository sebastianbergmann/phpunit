<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class BarTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
