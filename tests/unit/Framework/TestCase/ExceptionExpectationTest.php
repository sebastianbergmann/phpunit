<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use Exception as PhpException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ErrorLogNotWritableException;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Success;
use RuntimeException;

#[CoversClass(ExceptionExpectation::class)]
#[Small]
final class ExceptionExpectationTest extends TestCase
{
    public function testFreshExpectationDoesNotNeedVerification(): void
    {
        $expectation = new ExceptionExpectation;

        $this->assertFalse($expectation->shouldBeVerifiedFor(new RuntimeException));
    }

    public function testNeedsVerificationOnceClassIsExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(RuntimeException::class);

        $this->assertTrue($expectation->shouldBeVerifiedFor(new RuntimeException));
    }

    public function testNeedsVerificationOnceCodeIsExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectCode(42);

        $this->assertTrue($expectation->shouldBeVerifiedFor(new RuntimeException));
    }

    public function testNeedsVerificationOnceMessageIsExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageIs('boom');

        $this->assertTrue($expectation->shouldBeVerifiedFor(new RuntimeException));
    }

    public function testNeedsVerificationOnceMessageRegularExpressionIsExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageMatches('/boom/');

        $this->assertTrue($expectation->shouldBeVerifiedFor(new RuntimeException));
    }

    #[TestDox('PHPUnit framework exceptions are not verified unless explicitly expected')]
    public function testFrameworkExceptionsAreNotVerifiedByDefault(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(RuntimeException::class);

        $this->assertFalse($expectation->shouldBeVerifiedFor(new ErrorLogNotWritableException));
    }

    #[TestDox('When the expected class is PHPUnit\\Framework\\Exception, framework exceptions are verified')]
    public function testFrameworkExceptionsAreVerifiedWhenExpectedClassIsFrameworkException(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(PHPUnitException::class);

        $this->assertTrue($expectation->shouldBeVerifiedFor(new ErrorLogNotWritableException));
    }

    #[TestDox('When the expected class is a subclass of PHPUnit\\Framework\\Exception, framework exceptions are verified')]
    public function testFrameworkExceptionsAreVerifiedWhenExpectedClassIsFrameworkExceptionSubclass(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(ErrorLogNotWritableException::class);

        $this->assertTrue($expectation->shouldBeVerifiedFor(new ErrorLogNotWritableException));
    }

    #[TestDox('Leading backslash on PHPUnit\\Framework\\Exception is honoured')]
    public function testLeadingBackslashOnFrameworkExceptionIsHonoured(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass('\\PHPUnit\\Framework\\Exception');

        $this->assertTrue($expectation->shouldBeVerifiedFor(new ErrorLogNotWritableException));
    }

    public function testVerifyAcceptsMatchingClass(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(RuntimeException::class);

        $expectation->verify(new RuntimeException);
    }

    public function testVerifyRejectsMismatchedClass(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(RuntimeException::class);

        $this->expectException(ExpectationFailedException::class);

        $expectation->verify(new PhpException);
    }

    public function testVerifyAcceptsMatchingMessage(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageIs('boom');

        $expectation->verify(new RuntimeException('boom'));
    }

    public function testVerifyAcceptsMessageThatContainsExpectation(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageIsOrContains('oo');

        $expectation->verify(new RuntimeException('boom'));
    }

    public function testVerifyRejectsMismatchedMessage(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageIs('boom');

        $this->expectException(ExpectationFailedException::class);

        $expectation->verify(new RuntimeException('quiet'));
    }

    public function testVerifyAcceptsMatchingMessageRegularExpression(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageMatches('/^bo+m$/');

        $expectation->verify(new RuntimeException('boom'));
    }

    public function testVerifyRejectsMismatchedMessageRegularExpression(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageMatches('/^bo+m$/');

        $this->expectException(ExpectationFailedException::class);

        $expectation->verify(new RuntimeException('quiet'));
    }

    public function testVerifyAcceptsMatchingCode(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectCode(7);

        $expectation->verify(new RuntimeException('', 7));
    }

    public function testVerifyRejectsMismatchedCode(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectCode(7);

        $this->expectException(ExpectationFailedException::class);

        $expectation->verify(new RuntimeException('', 99));
    }

    public function testAssertWasRaisedDoesNothingWhenNoExpectationActive(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->assertWasRaised(new Success('testOne'));

        $this->expectNotToPerformAssertions();
    }

    public function testAssertWasRaisedFailsWhenClassWasExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectClass(RuntimeException::class);

        $this->expectException(ExpectationFailedException::class);

        $expectation->assertWasRaised(new Success('testOne'));
    }

    public function testAssertWasRaisedFailsWhenMessageWasExpectedAndBumpsAssertionCount(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageIs('boom');

        $test = new Success('testOne');

        try {
            $expectation->assertWasRaised($test);

            $this->fail('AssertionFailedError was not thrown');
        } catch (AssertionFailedError $e) {
            $this->assertSame(
                'Failed asserting that exception with message is "boom" is thrown',
                $e->getMessage(),
            );

            $this->assertSame(1, $test->numberOfAssertionsPerformed());
        }
    }

    public function testAssertWasRaisedFailsWhenMessageContainsWasExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageIsOrContains('oo');

        $test = new Success('testOne');

        try {
            $expectation->assertWasRaised($test);

            $this->fail('AssertionFailedError was not thrown');
        } catch (AssertionFailedError $e) {
            $this->assertSame(
                'Failed asserting that exception with message containing "oo" is thrown',
                $e->getMessage(),
            );

            $this->assertSame(1, $test->numberOfAssertionsPerformed());
        }
    }

    public function testAssertWasRaisedFailsWhenMessageRegularExpressionWasExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectMessageMatches('/boom/');

        $test = new Success('testOne');

        try {
            $expectation->assertWasRaised($test);

            $this->fail('AssertionFailedError was not thrown');
        } catch (AssertionFailedError $e) {
            $this->assertSame(
                'Failed asserting that exception with message matching "/boom/" is thrown',
                $e->getMessage(),
            );

            $this->assertSame(1, $test->numberOfAssertionsPerformed());
        }
    }

    public function testAssertWasRaisedFailsWhenCodeWasExpected(): void
    {
        $expectation = new ExceptionExpectation;

        $expectation->expectCode(7);

        $test = new Success('testOne');

        try {
            $expectation->assertWasRaised($test);

            $this->fail('AssertionFailedError was not thrown');
        } catch (AssertionFailedError $e) {
            $this->assertSame(
                'Failed asserting that exception with code "7" is thrown',
                $e->getMessage(),
            );

            $this->assertSame(1, $test->numberOfAssertionsPerformed());
        }
    }
}
