<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\ResultPrinter;

class TestDoxPrinter extends ResultPrinter
{
    /**
     * @var NamePrettifier
     */
    protected $prettifier;

    /**
     * @var int The number of test results received from the TestRunner
     */
    protected $testIndex = 0;

    /**
     * @var int The number of test results already sent to the output
     */
    protected $testFlushIndex = 0;

    /**
     * @var array<int, array> Buffer for test results
     */
    protected $testResults = [];

    /**
     * @var array<string, int> Lookup table for testname to testResults[index]
     */
    protected $testNameResultIndex = [];

    /**
     * @var bool
     */
    protected $enableOutputBuffer = false;

    /**
     * @var array array<string>
     */
    protected $originalExecutionOrder = [];

    public function __construct(
        $out = null,
        bool $verbose = false,
        $colors = self::COLOR_DEFAULT,
        bool $debug = false,
        $numberOfColumns = 80,
        bool $reverse = false
    ) {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $this->prettifier = new NamePrettifier($this->colors);
    }

    public function setOriginalExecutionOrder(array $order): void
    {
        $this->originalExecutionOrder = $order;
        $this->enableOutputBuffer     = !empty($order);
    }

    public function printResult(TestResult $result): void
    {
    }

    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase && !$test instanceof TestSuite) {
            return;
        }

        if ($this->testHasPassed()) {
            $this->registerTestResult($test, null, BaseTestRunner::STATUS_PASSED, $time, false);
        }

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $this->testIndex++;
        }

        parent::endTest($test, $time);
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_ERROR, $time, true);
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->registerTestResult($test, $e, BaseTestRunner::STATUS_WARNING, $time, true);
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->registerTestResult($test, $e, BaseTestRunner::STATUS_FAILURE, $time, true);
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_INCOMPLETE, $time, false);
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_RISKY, $time, false);
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_SKIPPED, $time, false);
    }

    public function writeProgress(string $progress): void
    {
        $this->flushOutputBuffer();
    }

    public function flush(): void
    {
        $this->flushOutputBuffer();
    }

    protected function registerTestResult(Test $test, ?\Throwable $t, int $status, float $time, bool $verbose): void
    {
        $testName = TestSuiteSorter::getTestSorterUID($test);
        $status   = $status ?? BaseTestRunner::STATUS_UNKNOWN;

        if ($t === null) {
            $resultMessage = '';
        } else {
            $resultMessage = $this->formatTestResultMessage(
                $this->formatThrowable($t, $status),
                $status,
                $verbose
            );
        }

        $this->testResults[$this->testIndex] = [
            'className'  => $this->formatClassName($test),
            'testName'   => $testName,
            'testMethod' => $this->formatTestName($test),
            'message'    => $resultMessage,
            'status'     => $status,
            'time'       => $time,
        ];

        $this->testNameResultIndex[$testName] = $this->testIndex;
    }

    protected function formatTestName(Test $test): string
    {
        return $test->getName();
    }

    protected function formatClassName(Test $test): string
    {
        return \get_class($test);
    }

    protected function testHasPassed(): bool
    {
        if (!isset($this->testResults[$this->testIndex]['status'])) {
            return true;
        }

        if ($this->testResults[$this->testIndex]['status'] === BaseTestRunner::STATUS_PASSED) {
            return true;
        }

        return false;
    }

    protected function flushOutputBuffer(): void
    {
        if ($this->testFlushIndex === $this->testIndex) {
            return;
        }

        if ($this->testFlushIndex > 0) {
            if ($this->enableOutputBuffer) {
                $prevResult = $this->getTestResultByName($this->originalExecutionOrder[$this->testFlushIndex - 1]);
            } else {
                $prevResult = $this->testResults[$this->testFlushIndex - 1];
            }
        } else {
            $prevResult = $this->getEmptyTestResult();
        }

        if (!$this->enableOutputBuffer) {
            $this->writeTestResult($prevResult, $this->testResults[$this->testFlushIndex++]);
        } else {
            do {
                $flushed = false;
                $result  = $this->getTestResultByName($this->originalExecutionOrder[$this->testFlushIndex]);

                if (!empty($result)) {
                    $this->writeTestResult($prevResult, $result);
                    $this->testFlushIndex++;
                    $prevResult = $result;
                    $flushed    = true;
                }
            } while ($flushed && $this->testFlushIndex < $this->testIndex);
        }
    }

    protected function writeTestResult(array $prevResult, array $result): void
    {
    }

    protected function getEmptyTestResult(): array
    {
        return [
            'className' => '',
            'testName'  => '',
            'message'   => '',
            'failed'    => '',
            'verbose'   => '',
        ];
    }

    protected function getTestResultByName(?string $testName): array
    {
        if (isset($this->testNameResultIndex[$testName])) {
            return $this->testResults[$this->testNameResultIndex[$testName]];
        }

        return [];
    }

    protected function formatThrowable(\Throwable $t, ?int $status = null): string
    {
        $message = \PHPUnit\Framework\TestFailure::exceptionToString($t);

        return \sprintf(
            "%s\n%s",
            $message,
            $this->formatStacktrace($t)
        );
    }

    protected function formatStacktrace(\Throwable $t): string
    {
        return \PHPUnit\Util\Filter::getFilteredStacktrace($t);
    }

    protected function formatTestResultMessage(string $message, int $status, bool $verbose, string $prefix = '│'): string
    {
        if ($message === '') {
            return '';
        }

        if (!($this->verbose || $verbose)) {
            return '';
        }

        return \sprintf(
            "   %s\n%s\n",
            $prefix,
            \implode(
                "\n",
                \array_map(
                    function (string $text) use ($prefix) {
                        return \sprintf('   %s %s', $prefix, $text);
                    },
                    \explode("\n", $message)
                )
            )
        );
    }
}
