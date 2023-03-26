<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestRunnerStopping;

use PHPUnit\Framework\TestCase;

final class IncompleteTest extends TestCase
{
    public function testOne(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
