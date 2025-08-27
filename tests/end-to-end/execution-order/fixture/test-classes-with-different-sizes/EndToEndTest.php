<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder\DifferentSizes;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;

#[Large]
#[CoversNothing]
final class EndToEndTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
