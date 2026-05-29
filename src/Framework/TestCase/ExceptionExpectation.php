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

use function class_exists;
use function is_string;
use function sprintf;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\Constraint\ExceptionCode;
use PHPUnit\Framework\Constraint\ExceptionMessageIs;
use PHPUnit\Framework\Constraint\ExceptionMessageIsOrContains;
use PHPUnit\Framework\Constraint\ExceptionMessageMatchesRegularExpression;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExceptionExpectation
{
    private ?string $expectedException                = null;
    private ?string $expectedMessage                  = null;
    private ?Constraint $expectedMessageConstraint    = null;
    private ?string $expectedMessageRegularExpression = null;
    private null|int|string $expectedCode             = null;

    /**
     * @param class-string<Throwable> $exception
     */
    public function expectClass(string $exception): void
    {
        $this->expectedException = $exception;
    }

    public function expectCode(int|string $code): void
    {
        $this->expectedCode = $code;
    }

    public function expectMessageIs(string $message): void
    {
        $this->expectedMessage           = $message;
        $this->expectedMessageConstraint = new ExceptionMessageIs($message);
    }

    public function expectMessageIsOrContains(string $message): void
    {
        $this->expectedMessage           = $message;
        $this->expectedMessageConstraint = new ExceptionMessageIsOrContains($message);
    }

    public function expectMessageMatches(string $regularExpression): void
    {
        $this->expectedMessageRegularExpression = $regularExpression;
    }

    /**
     * @throws Exception
     */
    public function shouldBeVerifiedFor(Throwable $throwable): bool
    {
        $result = false;

        if ($this->expectedException !== null || $this->expectedCode !== null || $this->expectedMessage !== null || $this->expectedMessageRegularExpression !== null) {
            $result = true;
        }

        if ($throwable instanceof Exception) {
            $result = false;
        }

        if (is_string($this->expectedException) && class_exists($this->expectedException)) {
            $reflector = new ReflectionClass($this->expectedException);

            if ($this->expectedException === 'PHPUnit\Framework\Exception' ||
                $this->expectedException === '\PHPUnit\Framework\Exception' ||
                $reflector->isSubclassOf(Exception::class)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @throws ExpectationFailedException
     */
    public function verify(Throwable $exception): void
    {
        if ($this->expectedException !== null) {
            Assert::assertThat(
                $exception,
                new ExceptionConstraint(
                    $this->expectedException,
                ),
            );
        }

        if ($this->expectedMessageConstraint !== null) {
            Assert::assertThat(
                $exception->getMessage(),
                $this->expectedMessageConstraint,
            );
        }

        if ($this->expectedMessageRegularExpression !== null) {
            Assert::assertThat(
                $exception->getMessage(),
                new ExceptionMessageMatchesRegularExpression(
                    $this->expectedMessageRegularExpression,
                ),
            );
        }

        if ($this->expectedCode !== null) {
            Assert::assertThat(
                $exception->getCode(),
                new ExceptionCode(
                    $this->expectedCode,
                ),
            );
        }
    }

    /**
     * @throws AssertionFailedError
     */
    public function assertWasRaised(TestCase $test): void
    {
        if ($this->expectedException !== null) {
            Assert::assertThat(
                null,
                new ExceptionConstraint($this->expectedException),
            );
        } elseif ($this->expectedMessageConstraint !== null) {
            $test->addToAssertionCount(1);

            throw new AssertionFailedError(
                sprintf(
                    'Failed asserting that exception with message %s "%s" is thrown',
                    $this->expectedMessageConstraint instanceof ExceptionMessageIs ? 'is' : 'containing',
                    $this->expectedMessage,
                ),
            );
        } elseif ($this->expectedMessageRegularExpression !== null) {
            $test->addToAssertionCount(1);

            throw new AssertionFailedError(
                sprintf(
                    'Failed asserting that exception with message matching "%s" is thrown',
                    $this->expectedMessageRegularExpression,
                ),
            );
        } elseif ($this->expectedCode !== null) {
            $test->addToAssertionCount(1);

            throw new AssertionFailedError(
                sprintf(
                    'Failed asserting that exception with code "%s" is thrown',
                    $this->expectedCode,
                ),
            );
        }
    }
}
