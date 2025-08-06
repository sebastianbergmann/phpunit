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
use PHPUnit\Runner\ErrorHandler;
use Throwable;

class Issue5592Test extends TestCase
{
    public function testAddedAndRemovedErrorHandler(): void
    {
        $previous = set_error_handler([$this, 'addedAndRemovedErrorHandler']);
        restore_error_handler();

        $this->assertInstanceOf(ErrorHandler::class, $previous);
        $this->assertTrue(true);
    }

    public function testAddedErrorHandler(): void
    {
        $previous = set_error_handler([$this, 'addedErrorHandler']);

        $this->assertInstanceOf(ErrorHandler::class, $previous);
        $this->assertTrue(false);
    }

    public function testRemovedErrorHandler(): void
    {
        restore_error_handler();
        $this->assertTrue(false);
    }

    public function testAddedAndRemovedExceptionHandler(): void
    {
        $previous = set_exception_handler([$this, 'addedAndRemovedExceptionHandler']);
        restore_exception_handler();

        $this->assertSame('global5592ExceptionHandler', $previous);
        $this->assertTrue(true);
    }

    public function testAddedExceptionHandler(): void
    {
        $previous = set_exception_handler([$this, 'addedExceptionHandler']);

        $this->assertSame('global5592ExceptionHandler', $previous);
        $this->assertTrue(false);
    }

    public function testRemovedExceptionHandler(): void
    {
        restore_exception_handler();
        $this->assertTrue(false);
    }

    public function addedAndRemovedErrorHandler($errno, $errstr, $errfile, $errline): bool
    {
        return false;
    }

    public function addedErrorHandler($errno, $errstr, $errfile, $errline): bool
    {
        return false;
    }

    public function addedAndRemovedExceptionHandler(Throwable $exception): void
    {
    }

    public function addedExceptionHandler(Throwable $exception): void
    {
    }
}
