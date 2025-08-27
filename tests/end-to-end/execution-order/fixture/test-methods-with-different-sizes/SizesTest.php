<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ExecutionOrder\DifferentSizes;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class SizesTest extends TestCase
{
    /**
     * @medium
     */
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @large
     */
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @small
     */
    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
