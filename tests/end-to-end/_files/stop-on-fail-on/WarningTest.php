<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestRunnerStopping;

use PHPUnit\Framework\TestCase;

final class WarningTest extends TestCase
{
    public function testOne(): void
    {
        trigger_error('message', E_USER_WARNING);

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
