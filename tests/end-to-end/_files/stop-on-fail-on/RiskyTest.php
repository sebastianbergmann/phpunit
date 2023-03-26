<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestRunnerStopping;

use PHPUnit\Framework\TestCase;

final class RiskyTest extends TestCase
{
    public function testOne(): void
    {
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
