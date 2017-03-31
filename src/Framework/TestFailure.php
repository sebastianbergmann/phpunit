<?php
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
use Throwable;

/**
 * A TestFailure collects a failed test together with the caught exception.
 */
class TestFailure
{
    /**
     * @var string
     */
    private $testName;

    /**
     * @var Test|null
     */
    protected $failedTest;

    /**
     * @var Throwable
     */
    protected $thrownException;

    /**
     * Constructs a TestFailure with the given test and exception.
     *
     * @param Test      $failedTest
     * @param Throwable $t
     */
    public function __construct(Test $failedTest, $t)
    {
        if ($failedTest instanceof SelfDescribing) {
            $this->testName = $failedTest->toString();
        } else {
            $this->testName = \get_class($failedTest);
        }

        if (!$failedTest instanceof TestCase || !$failedTest->isInIsolation()) {
            $this->failedTest = $failedTest;
        }

        $this->thrownException = $t;
    }

    /**
     * Returns a short description of the failure.
     *
     * @return string
     */
    public function toString()
    {
        return \sprintf(
            '%s: %s',
            $this->testName,
            $this->thrownException->getMessage()
        );
    }

    /**
     * Returns a description for the thrown exception.
     *
     * @return string
     */
    public function getExceptionAsString()
    {
        return self::exceptionToString($this->thrownException);
    }

    /**
     * Returns a description for an exception.
     *
     * @param Throwable $e
     *
     * @return string
     */
    public static function exceptionToString(Throwable $e)
    {
        if ($e instanceof SelfDescribing) {
            $buffer = $e->toString();

            if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
                $buffer .= $e->getComparisonFailure()->getDiff();
            }

            if (!empty($buffer)) {
                $buffer = \trim($buffer) . "\n";
            }

            return $buffer;
        }

        if ($e instanceof Error) {
            return $e->getMessage() . "\n";
        }

        if ($e instanceof ExceptionWrapper) {
            return $e->getClassName() . ': ' . $e->getMessage() . "\n";
        }

        return \get_class($e) . ': ' . $e->getMessage() . "\n";
    }

    /**
     * Returns the name of the failing test (including data set, if any).
     *
     * @return string
     */
    public function getTestName()
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
     *
     * @return Test|null
     */
    public function failedTest()
    {
        return $this->failedTest;
    }

    /**
     * Gets the thrown exception.
     *
     * @return Throwable
     */
    public function thrownException()
    {
        return $this->thrownException;
    }

    /**
     * Returns the exception's message.
     *
     * @return string
     */
    public function exceptionMessage()
    {
        return $this->thrownException()->getMessage();
    }

    /**
     * Returns true if the thrown exception
     * is of type AssertionFailedError.
     *
     * @return bool
     */
    public function isFailure()
    {
        return ($this->thrownException() instanceof AssertionFailedError);
    }
}
