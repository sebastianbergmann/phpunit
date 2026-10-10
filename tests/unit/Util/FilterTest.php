<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\Framework\PhptAssertionFailedError;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filter::class)]
#[Small]
final class FilterTest extends TestCase
{
    public function testUnwrapThrowableUsesPreviousValues(): void
    {
        $first  = new Exception('first', 123, null);
        $second = new Exception('second', 345, $first);

        $this->assertSame(Filter::stackTraceFromThrowableAsString($second), Filter::stackTraceFromThrowableAsString($first));
    }

    public function testStackTraceFromPhptAssertionFailedError(): void
    {
        $e = new PhptAssertionFailedError(
            'phpt assertion failed',
            0,
            '/tmp/test.phpt',
            42,
            [
                ['file' => '/tmp/test.phpt', 'line' => 42, 'function' => 'main', 'type' => '->'],
            ],
            'expected diff',
        );

        $result = Filter::stackTraceFromThrowableAsString($e);

        $this->assertIsString($result);
    }

    public function testStackTraceFromFrameworkException(): void
    {
        $e = new FrameworkException('framework exception');

        $result = Filter::stackTraceFromThrowableAsString($e);

        $this->assertIsString($result);
    }

    public function testStackTraceFromThrowableWithoutPreviousDoesNotUnwrap(): void
    {
        $e = new Exception('no previous');

        $result = Filter::stackTraceFromThrowableAsString($e);

        $this->assertIsString($result);
    }

    public function testFirstFrameFromThrowableIsTheFirstLineOfItsStackTrace(): void
    {
        $e = new Exception('message');

        $this->assertSame(__FILE__ . ':' . (__LINE__ - 2), Filter::firstFrameFromThrowableAsString($e));
    }

    public function testFirstFrameFromThrowableIsTakenFromThePreviousThrowableWhenItIsUnwrapped(): void
    {
        $first  = new Exception('first');
        $second = new Exception('second', 0, $first);

        $this->assertSame(__FILE__ . ':' . (__LINE__ - 3), Filter::firstFrameFromThrowableAsString($second));
        $this->assertSame(__FILE__ . ':' . (__LINE__ - 3), Filter::firstFrameFromThrowableAsString($second, false));
    }

    public function testFirstFrameFromThrowableIsNullWhenItsStackTraceIsEmpty(): void
    {
        $e = new PhptAssertionFailedError(
            'phpt assertion failed',
            0,
            '/does/not/exist.phpt',
            42,
            [
                ['file' => '/does/not/exist.phpt', 'line' => 42, 'function' => 'main', 'type' => '->'],
            ],
            'expected diff',
        );

        $this->assertSame('', Filter::stackTraceFromThrowableAsString($e));
        $this->assertNull(Filter::firstFrameFromThrowableAsString($e));
    }

    public function testStackTraceFromThrowableWithUnwrapFalse(): void
    {
        $first  = new Exception('first');
        $second = new Exception('second', 0, $first);

        $result = Filter::stackTraceFromThrowableAsString($second, false);

        $this->assertIsString($result);
    }
}
