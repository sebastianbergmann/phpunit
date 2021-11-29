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

use function sprintf;
use function trim;
use PHPUnit\Util\Error\Error;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestFailure
{
    private ?Test $failedTest = null;
    private Throwable $thrownException;
    private string $testName;

    /**
     * Returns a description for an exception.
     */
    public static function exceptionToString(Throwable $e): string
    {
        if ($e instanceof SelfDescribing) {
            $buffer = $e->toString();

            if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
                $buffer .= $e->getComparisonFailure()->getDiff();
            }

            if ($e instanceof PHPTAssertionFailedError) {
                $buffer .= $e->getDiff();
            }

            if (!empty($buffer)) {
                $buffer = trim($buffer) . "\n";
            }

            return $buffer;
        }

        if ($e instanceof Error) {
            return $e->getMessage() . "\n";
        }

        if ($e instanceof ExceptionWrapper) {
            return $e->getClassName() . ': ' . $e->getMessage() . "\n";
        }

        return $e::class . ': ' . $e->getMessage() . "\n";
    }

    /**
     * Constructs a TestFailure with the given test and exception.
     */
    public function __construct(Test $failedTest, Throwable $t)
    {
        $this->testName = $this->describe($failedTest);

        if (!$failedTest instanceof TestCase || !$failedTest->isInIsolation()) {
            $this->failedTest = $failedTest;
        }

        $this->thrownException = $t;
    }

    /**
     * Returns a short description of the failure.
     */
    public function toString(): string
    {
        return sprintf(
            '%s: %s',
            $this->testName,
            $this->thrownException->getMessage()
        );
    }

    /**
     * Returns a description for the thrown exception.
     */
    public function getExceptionAsString(): string
    {
        return self::exceptionToString($this->thrownException);
    }

    /**
     * Returns the name of the failing test (including data set, if any).
     */
    public function getTestName(): string
    {
        return $this->testName;
    }

    /**
     * Returns the failing test.
     *
     * Note: The test object is not set when the test is executed in process
     * isolation.
     *
     * @see Exception
     */
    public function failedTest(): ?Test
    {
        return $this->failedTest;
    }

    /**
     * Gets the thrown exception.
     */
    public function thrownException(): Throwable
    {
        return $this->thrownException;
    }

    /**
     * Returns the exception's message.
     */
    public function exceptionMessage(): string
    {
        return $this->thrownException()->getMessage();
    }

    /**
     * Returns true if the thrown exception
     * is of type AssertionFailedError.
     */
    public function isFailure(): bool
    {
        return $this->thrownException() instanceof AssertionFailedError;
    }

    private function describe(Test $test): string
    {
        if ($test instanceof SelfDescribing) {
            return $test->toString();
        }

        return $test::class;
    }
}
