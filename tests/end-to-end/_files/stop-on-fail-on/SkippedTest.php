<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestRunnerStopping;

use PHPUnit\Framework\TestCase;

final class SkippedTest extends TestCase
{
    public function testOne(): void
    {
        $this->markTestSkipped('message');
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
