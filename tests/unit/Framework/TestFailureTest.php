<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Error\Error;
use PHPUnit\TestFixture\NotSelfDescribingTest;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @small
 */
final class TestFailureTest extends TestCase
{
    public function testToString(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new Exception('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals(__METHOD__ . ': message', $failure->toString());
    }

    public function testToStringForError(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new \Error('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals(__METHOD__ . ': message', $failure->toString());
    }

    public function testToStringForNonSelfDescribing(): void
    {
        $test      = new NotSelfDescribingTest();
        $exception = new Exception('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals('PHPUnit\TestFixture\NotSelfDescribingTest: message', $failure->toString());
    }

    public function testgetExceptionAsString(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new \Error('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals("Error: message\n", $failure->getExceptionAsString());
    }

    public function testExceptionToString(): void
    {
        $exception = new AssertionFailedError('message');

        $this->assertEquals("message\n", TestFailure::exceptionToString($exception));
    }

    public function testExceptionToStringForExpectationFailedException(): void
    {
        $exception = new ExpectationFailedException('message');

        $this->assertEquals("message\n", TestFailure::exceptionToString($exception));
    }

    public function testExceptionToStringForExpectationFailedExceptionWithComparisonFailure(): void
    {
        $exception = new ExpectationFailedException('message', new ComparisonFailure('expected', 'actual', 'expected', 'actual'));

        $this->assertEquals("message\n--- Expected\n+++ Actual\n@@ @@\n-expected\n+actual\n", TestFailure::exceptionToString($exception));
    }

    public function testExceptionToStringForFrameworkError(): void
    {
        $exception = new Error('message', 0, 'file', 1);

        $this->assertEquals("message\n", TestFailure::exceptionToString($exception));
    }

    public function testExceptionToStringForExceptionWrapper(): void
    {
        $exception = new ExceptionWrapper(new \Error('message'));

        $this->assertEquals("Error: message\n", TestFailure::exceptionToString($exception));
    }

    public function testGetTestName(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new Exception('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals($this->toString(), $failure->getTestName());
    }

    public function testFailedTest(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new Exception('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals($test, $failure->failedTest());
    }

    public function testThrownException(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new Exception('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals($exception, $failure->thrownException());
    }

    public function testExceptionMessage(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new Exception('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertEquals('message', $failure->exceptionMessage());
    }

    public function testIsFailure(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new ExpectationFailedException('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertTrue($failure->isFailure());
    }

    public function testIsFailureFalse(): void
    {
        $test      = new self(__FUNCTION__);
        $exception = new Warning('message');
        $failure   = new TestFailure($test, $exception);

        $this->assertFalse($failure->isFailure());
    }
}
