<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestRunnerStopping;

use PHPUnit\Framework\TestCase;

final class FailureTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(false);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
