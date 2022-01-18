<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use const PHP_EOL;
use function array_map;
use function implode;
use function method_exists;
use function preg_split;
use function trim;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\Util\Filter;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class TestDoxPrinter extends DefaultResultPrinter
{
    protected NamePrettifier $prettifier;

    /**
     * @var int The number of test results received from the TestRunner
     */
    protected int $testIndex = 0;

    /**
     * @var int The number of test results already sent to the output
     */
    protected int $testFlushIndex = 0;

    /**
     * @psalm-var array<int, array> Buffer for test results
     */
    protected array $testResults = [];

    /**
     * @psalm-var array<string, int> Lookup table for testname to testResults[index]
     */
    protected array $testNameResultIndex = [];
    protected bool $enableOutputBuffer   = false;

    /**
     * @psalm-var array<string>
     */
    protected array $originalExecutionOrder = [];

    /**
     * @psalm-var 0|positive-int
     */
    protected int $spinState     = 0;
    protected bool $showProgress = true;

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(string $out, bool $verbose = false, bool $colors = false, int|string $numberOfColumns = 80, bool $reverse = false)
    {
        parent::__construct($out, $verbose, $colors, $numberOfColumns, $reverse);

        $this->prettifier = new NamePrettifier($this->colors);
    }

    public function setOriginalExecutionOrder(array $order): void
    {
        $this->originalExecutionOrder = $order;
        $this->enableOutputBuffer     = !empty($order);
    }

    public function setShowProgressAnimation(bool $showProgress): void
    {
        $this->showProgress = $showProgress;
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
            $this->registerTestResult($test, null, TestStatus::success(), $time, false);
        }

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $this->testIndex++;
        }

        parent::endTest($test, $time);
    }

    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, TestStatus::error(), $time, true);
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->registerTestResult($test, $e, TestStatus::warning(), $time, true);
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->registerTestResult($test, $e, TestStatus::failure(), $time, true);
    }

    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, TestStatus::incomplete(), $time, false);
    }

    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, TestStatus::risky(), $time, false);
    }

    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, TestStatus::skipped(), $time, false);
    }

    public function writeProgress(string $progress): void
    {
        $this->flushOutputBuffer();
    }

    public function flush(): void
    {
        $this->flushOutputBuffer(true);
    }

    protected function registerTestResult(Test $test, ?Throwable $t, TestStatus $status, float $time, bool $verbose): void
    {
        $testName = $test instanceof Reorderable ? $test->sortId() : $test->getName();

        $result = [
            'className'  => $this->formatClassName($test),
            'testName'   => $testName,
            'testMethod' => $this->formatTestName($test),
            'message'    => '',
            'status'     => $status,
            'time'       => $time,
            'verbose'    => $verbose,
        ];

        if ($t !== null) {
            $result['message'] = $this->formatTestResultMessage($t, $result);
        }

        $this->testResults[$this->testIndex]  = $result;
        $this->testNameResultIndex[$testName] = $this->testIndex;
    }

    protected function formatTestName(Test $test): string
    {
        return method_exists($test, 'getName') ? $test->getName() : '';
    }

    protected function formatClassName(Test $test): string
    {
        return $test::class;
    }

    protected function testHasPassed(): bool
    {
        if (!isset($this->testResults[$this->testIndex]['status'])) {
            return true;
        }

        if ($this->testResults[$this->testIndex]['status']->isSuccess()) {
            return true;
        }

        return false;
    }

    protected function flushOutputBuffer(bool $forceFlush = false): void
    {
        if ($this->testFlushIndex === $this->testIndex) {
            return;
        }

        if ($this->testFlushIndex > 0) {
            if ($this->enableOutputBuffer &&
                isset($this->originalExecutionOrder[$this->testFlushIndex - 1])) {
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

                if (!$forceFlush && isset($this->originalExecutionOrder[$this->testFlushIndex])) {
                    $result = $this->getTestResultByName($this->originalExecutionOrder[$this->testFlushIndex]);
                } else {
                    // This test(name) cannot found in original execution order,
                    // flush result to output stream right away
                    $result = $this->testResults[$this->testFlushIndex];
                }

                if (!empty($result)) {
                    $this->hideSpinner();
                    $this->writeTestResult($prevResult, $result);
                    $this->testFlushIndex++;
                    $prevResult = $result;
                    $flushed    = true;
                } else {
                    $this->showSpinner();
                }
            } while ($flushed && $this->testFlushIndex < $this->testIndex);
        }
    }

    protected function showSpinner(): void
    {
        if (!$this->showProgress) {
            return;
        }

        if ($this->spinState) {
            $this->undrawSpinner();
        }

        $this->spinState++;
        $this->drawSpinner();
    }

    protected function hideSpinner(): void
    {
        if (!$this->showProgress) {
            return;
        }

        if ($this->spinState) {
            $this->undrawSpinner();
        }

        $this->spinState = 0;
    }

    protected function drawSpinner(): void
    {
        // optional for CLI printers: show the user a 'buffering output' spinner
    }

    protected function undrawSpinner(): void
    {
        // remove the spinner from the current line
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

    protected function formatThrowable(Throwable $t): string
    {
        $message = trim(TestFailure::exceptionToString($t));

        if ($message) {
            $message .= PHP_EOL . PHP_EOL . $this->formatStacktrace($t);
        } else {
            $message = $this->formatStacktrace($t);
        }

        return $message;
    }

    protected function formatStacktrace(Throwable $t): string
    {
        return Filter::getFilteredStacktrace($t);
    }

    protected function formatTestResultMessage(Throwable $t, array $result, string $prefix = '│'): string
    {
        $message = $this->formatThrowable($t);

        if ($message === '') {
            return '';
        }

        if (!($this->verbose || $result['verbose'])) {
            return '';
        }

        return $this->prefixLines($prefix, $message);
    }

    protected function prefixLines(string $prefix, string $message): string
    {
        $message = trim($message);

        return implode(
            PHP_EOL,
            array_map(
                static function (string $text) use ($prefix)
                {
                    return '   ' . $prefix . ($text ? ' ' . $text : '');
                },
                preg_split('/\r\n|\r|\n/', $message)
            )
        );
    }
}
