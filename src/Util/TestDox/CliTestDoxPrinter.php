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
use SebastianBergmann\Timer\Timer;

/**
 * This printer is for CLI output only. For the classes that output to file, html and xml,
 * please refer to the PHPUnit\Util\TestDox namespace
 */
class CliTestDoxPrinter extends ResultPrinter
{
    /**
     * @var int[]
     */
    private $nonSuccessfulTestResults = [];

    /**
     * @var NamePrettifier
     */
    private $prettifier;

    /**
     * @var int The number of test results received from the TestRunner
     */
    private $testIndex = 0;

    /**
     * @var int The number of test results already sent to the output
     */
    private $testFlushIndex = 0;

    /**
     * @var array<int, array> Buffer for test results
     */
    private $testResults = [];

    /**
     * @var array<string, int> Lookup table for testname to testResults[index]
     */
    private $testNameResultIndex = [];

    /**
     * @var bool
     */
    private $enableOutputBuffer = false;

    /**
     * @var array array<string>
     */
    private $originalExecutionOrder = [];

    private $statusStyles = [
        BaseTestRunner::STATUS_PASSED => [
            'symbol' => '✔',
            'color'  => 'fg-green',
        ],
        BaseTestRunner::STATUS_ERROR => [
            'symbol' => '✘',
            'color'  => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_FAILURE => [
            'symbol' => '✘',
            'color'  => 'fg-red',
        ],
        BaseTestRunner::STATUS_SKIPPED => [
            'symbol' => '→',
            'color'  => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_RISKY => [
            'symbol' => '☢',
            'color'  => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_INCOMPLETE => [
            'symbol' => '∅',
            'color'  => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_WARNING => [
            'symbol' => '✘',
            'color'  => 'fg-yellow',
        ],
    ];

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

    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase && !$test instanceof TestSuite) {
            return;
        }

        if ($this->testHasPassed()) {
            $this->registerTestResult($test, BaseTestRunner::STATUS_PASSED, $time, '');
        }

        $this->flushOutputBuffer();

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $this->testIndex++;
        }

        parent::endTest($test, $time);
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $resultMessage = $this->formatTestResultMessage(
            $this->formatThrowable($t),
            true
        );
        $this->registerTestResult($test, BaseTestRunner::STATUS_ERROR, $time, $resultMessage);
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $resultMessage = $this->formatTestResultMessage(
            $this->formatThrowable($e),
            true
        );
        $this->registerTestResult($test, BaseTestRunner::STATUS_WARNING, $time, $resultMessage);
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $resultMessage = $this->formatTestResultMessage(
            $this->formatThrowable($e),
            true
        );
        $this->registerTestResult($test, BaseTestRunner::STATUS_FAILURE, $time, $resultMessage);
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $resultMessage = $this->formatTestResultMessage(
            $this->formatThrowable($t),
            false
        );
        $this->registerTestResult($test, BaseTestRunner::STATUS_INCOMPLETE, $time, $resultMessage);
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $resultMessage = $this->formatTestResultMessage(
            $this->formatThrowable($t),
            false
        );
        $this->registerTestResult($test, BaseTestRunner::STATUS_RISKY, $time, $resultMessage);
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $resultMessage = $this->formatTestResultMessage(
            $this->formatThrowable($t),
            false
        );
        $this->registerTestResult($test, BaseTestRunner::STATUS_SKIPPED, $time, $resultMessage);
    }

    public function registerTestResult(Test $test, int $status, float $time, string $msg): void
    {
        $testName                            = TestSuiteSorter::getTestSorterUID($test);
        $this->testResults[$this->testIndex] = [
            'className'  => $this->getPrettyClassName($test),
            'testName'   => $testName,
            'testMethod' => $this->getPrettyTestName($test),
            'message'    => $msg,
            'status'     => $status,
            'time'       => $time,
        ];

        $this->testNameResultIndex[$testName] = $this->testIndex;

        if ($status !== BaseTestRunner::STATUS_PASSED) {
            $this->nonSuccessfulTestResults[] = $this->testIndex;
        }
    }

    public function writeProgress(string $progress): void
    {
    }

    public function flush(): void
    {
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader();

        $this->printNonSuccessfulTestsSummary($result->count());

        $this->printFooter($result);
    }

    protected function printHeader(): void
    {
        $this->write("\n" . Timer::resourceUsage() . "\n\n");
    }

    private function getPrettyClassName(Test $test): string
    {
        if ($test instanceof TestCase) {
            return $this->prettifier->prettifyTestClass(\get_class($test));
        }

        return \get_class($test);
    }

    private function getPrettyTestName(Test $test): string
    {
        if ($test instanceof TestCase) {
            return $this->prettifier->prettifyTestCase($test);
        }

        return $test->getName();
    }

    private function testHasPassed(): bool
    {
        if (!isset($this->testResults[$this->testIndex]['status'])) {
            return true;
        }

        if ($this->testResults[$this->testIndex]['status'] === BaseTestRunner::STATUS_PASSED) {
            return true;
        }

        return false;
    }

    private function flushOutputBuffer(): void
    {
        if ($this->enableOutputBuffer && ($this->testFlushIndex === $this->testIndex)) {
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
            } while ($flushed && $this->testFlushIndex <= $this->testIndex);
        }
    }

    private function writeTestResult(array $prevResult, array $result): void
    {
        // spacer line for new suite headers and after verbose messages
        if ($prevResult['testName'] !== '' &&
            (!empty($prevResult['message']) || $prevResult['className'] !== $result['className'])) {
            $this->write("\n");
        }

        // suite header
        if ($prevResult['className'] !== $result['className']) {
            $this->write($this->formatWithColor('underlined', $result['className']) . "\n");
        }

        // test result line
        if ($result['className'] == PhptTestCase::class) {
            $testName = $this->colorizePath($result['testName'], $prevResult['testName']);
        } else {
            $testName = $result['testMethod'];
        }
        $style = $this->statusStyles[$result['status']];
        $line  = \sprintf(
            " %s %s%s\n",
            $this->formatWithColor($style['color'], $style['symbol']),
            $testName,
            $this->verbose ? ' ' . $this->getFormattedRuntime($result['time'], $style['color']) : ''
            );
        $this->write($line);

        // additional information when verbose
        $this->write($result['message']);
    }

    private function getTestResultByName(string $testName): array
    {
        if (isset($this->testNameResultIndex[$testName])) {
            return $this->testResults[$this->testNameResultIndex[$testName]];
        }

        return [];
    }

    private function getFormattedRuntime(float $time, string $color = ''): string
    {
        if ($time > 1) {
            return $this->formatWithColor('fg-magenta', \sprintf('[%.2f ms]', $time * 1000));
        }

        return $this->formatWithColor($color, \sprintf('[%.2f ms]', $time * 1000));
    }

    private function formatTestResultMessage(string $resultMessage, bool $verbose): string
    {
        if ($resultMessage === '') {
            return '';
        }

        if (!($this->verbose || $verbose)) {
            return '';
        }

        return \sprintf(
            "   │\n%s\n",
            \implode(
                "\n",
                \array_map(
                    function (string $text) {
                        return \sprintf('   │ %s', $text);
                    },
                    \explode("\n", $resultMessage)
                )
            )
        );
    }

    private function printNonSuccessfulTestsSummary(int $numberOfExecutedTests): void
    {
        if (empty($this->nonSuccessfulTestResults)) {
            return;
        }

        if ((\count($this->nonSuccessfulTestResults) / $numberOfExecutedTests) >= 0.7) {
            return;
        }

        $this->write("Summary of non-successful tests:\n\n");

        $prevResult = $this->getEmptyTestResult();

        foreach ($this->nonSuccessfulTestResults as $testIndex) {
            $result = $this->testResults[$testIndex];
            $this->writeTestResult($prevResult, $result);
            $prevResult = $result;
        }
    }

    private function getEmptyTestResult(): array
    {
        return [
            'className' => '',
            'testName'  => '',
            'message'   => '',
            'failed'    => '',
            'verbose'   => '',
        ];
    }

    private function formatThrowable(\Throwable $t): string
    {
        return \sprintf(
            "%s\n%s",
            \PHPUnit\Framework\TestFailure::exceptionToString($t),
            $this->colorizeStacktrace($t)
            );
    }

    private function colorizeStacktrace(\Throwable $t): string
    {
        $trace = \PHPUnit\Util\Filter::getFilteredStacktrace($t);

        if (!$this->colors) {
            return $trace;
        }

        $lines    = [];
        $prevPath = '';

        foreach (\explode("\n", $trace) as $line) {
            if (\preg_match('/^(.*):(\d+)$/', $line, $matches)) {
                $lines[] =  $this->colorizePath($matches[1], $prevPath) .
                            $this->formatWithColor('dim', ':') .
                            $this->formatWithColor('fg-blue', $matches[2]) .
                            "\n";
                $prevPath = $matches[1];
            } else {
                $lines[]  = $line;
                $prevPath = '';
            }
        }

        return \implode('', $lines);
    }

    private function colorizePath(string $path, ?string $prevPath): string
    {
        if ($prevPath === null) {
            $prevPath = '';
        }

        $path     = \explode(\DIRECTORY_SEPARATOR, $path);
        $prevPath = \explode(\DIRECTORY_SEPARATOR, $prevPath);

        for ($i = 0; $i < \min(\count($path), \count($prevPath)); $i++) {
            if ($path[$i] == $prevPath[$i]) {
                $path[$i] = $this->formatWithColor('dim', $path[$i]);
            }
        }

        return \implode($this->formatWithColor('dim', \DIRECTORY_SEPARATOR), $path);
    }
}
