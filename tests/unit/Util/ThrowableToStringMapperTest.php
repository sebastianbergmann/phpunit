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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\PhptAssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ErrorException;
use RuntimeException;
use SebastianBergmann\Comparator\ComparisonFailure;

#[CoversClass(ThrowableToStringMapper::class)]
#[Small]
#[TestDox('ThrowableToStringMapper')]
final class ThrowableToStringMapperTest extends TestCase
{
    #[TestDox('Maps ExpectationFailedException with ComparisonFailure')]
    public function testMapsExpectationFailedExceptionWithComparisonFailure(): void
    {
        $comparisonFailure = new ComparisonFailure('expected', 'actual', 'expected', 'actual');
        $e                 = new ExpectationFailedException('msg', $comparisonFailure);

        $mapped = ThrowableToStringMapper::map($e);

        $this->assertStringContainsString('msg', $mapped);
        $this->assertStringContainsString($comparisonFailure->getDiff(), $mapped);
        $this->assertStringEndsWith("\n", $mapped);
    }

    #[TestDox('Maps PhptAssertionFailedError')]
    public function testMapsPhptAssertionFailedError(): void
    {
        $error  = new PhptAssertionFailedError('phpt-message', 0, 'file', 1, [], 'my-diff-string');
        $mapped = ThrowableToStringMapper::map($error);

        $this->assertStringContainsString('phpt-message', $mapped);
        $this->assertStringContainsString('my-diff-string', $mapped);
        $this->assertStringEndsWith("\n", $mapped);
    }

    #[TestDox('Maps \ErrorException')]
    public function testMapsErrorException(): void
    {
        $e = new ErrorException('boom');

        $this->assertSame('boom', ThrowableToStringMapper::map($e));
    }

    #[TestDox('Maps \Throwable')]
    public function testMapsThrowable(): void
    {
        $t        = new RuntimeException('oops');
        $expected = RuntimeException::class . ': oops' . "\n";

        $this->assertSame($expected, ThrowableToStringMapper::map($t));
    }
}
