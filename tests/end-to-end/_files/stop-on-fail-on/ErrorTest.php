<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestRunnerStopping;

use Exception;
use PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
    public function testOne(): void
    {
        throw new Exception('message');
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
