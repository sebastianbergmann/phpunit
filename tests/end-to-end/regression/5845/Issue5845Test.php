<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function restore_error_handler;
use function restore_exception_handler;
use function set_error_handler;
use function set_exception_handler;
use PHPUnit\Framework\TestCase;
use Throwable;

class Issue5845Test extends TestCase
{
    public function testAddedAndRemovedErrorHandler(): void
    {
        set_error_handler($this->errorHandler(...));
        restore_error_handler();

        $this->assertTrue(true);
    }

    public function testAddedErrorHandler(): void
    {
        set_error_handler($this->errorHandler(...));

        $this->assertTrue(false);
    }

    public function testRemovedErrorHandler(): void
    {
        restore_error_handler();
        $this->assertTrue(false);
    }

    public function testAddedAndRemovedExceptionHandler(): void
    {
        $previous = set_exception_handler($this->exceptionHandler(...));
        restore_exception_handler();

        $this->assertSame('global5845ExceptionHandler', $previous);
        $this->assertTrue(true);
    }

    public function testAddedExceptionHandler(): void
    {
        $previous = set_exception_handler($this->exceptionHandler(...));

        $this->assertSame('global5845ExceptionHandler', $previous);
        $this->assertTrue(false);
    }

    public function testRemovedExceptionHandler(): void
    {
        restore_exception_handler();
        $this->assertTrue(false);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline): bool
    {
        return false;
    }

    public function exceptionHandler(Throwable $exception): void
    {
    }
}
