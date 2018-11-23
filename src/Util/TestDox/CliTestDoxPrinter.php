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
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\TestResultCache;
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
     * @var array Buffer for write()
     */
    private $outputBuffer = [];

    /**
     * @var bool
     */
    private $bufferExecutionOrder = false;

    /**
     * @var array array<string>
     */
    private $originalExecutionOrder = [];

    private $className;

    private $lastClassName;

    private $testMethod;

    private $msg;

    private $lastFlushedTestWasVerbose = false;

    public function __construct($out = null, bool $verbose = false, $colors = self::COLOR_DEFAULT, bool $debug = false, $numberOfColumns = 80, bool $reverse = false)
    {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $this->prettifier = new NamePrettifier;
    }

    public function setOriginalExecutionOrder(array $order): void
    {
        $this->originalExecutionOrder = $order;
        $this->bufferExecutionOrder   = !empty($order);
    }

    public function startTest(Test $test): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase && !$test instanceof TestSuite) {
            return;
        }

        $this->lastTestFailed = false;
        $this->lastClassName  = $this->className;
        $this->msg            = '';

        if ($test instanceof TestCase) {
            $className  = $this->prettifier->prettifyTestClass(\get_class($test));
            $testMethod = $this->prettifier->prettifyTestCase($test);
        } elseif ($test instanceof TestSuite) {
            $className  = $test->getName();
            $testMethod = \sprintf(
                'Error bootstapping suite (most likely in %s::setUpBeforeClass)',
                $test->getName()
            );
        } elseif ($test instanceof PhptTestCase) {
            $className  = \get_class($test);
            $testMethod = $test->getName();
        }

        $this->className      = $className;
        $this->testMethod     = $testMethod;

        parent::startTest($test);
    }

    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase && !$test instanceof TestSuite) {
            return;
        }

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $this->testIndex++;
        }

        if ($this->lastTestFailed) {
            $msg                              = $this->msg;
            $this->nonSuccessfulTestResults[] = $this->testIndex;
        } else {
            $msg = $this->formatTestResultMessage($this->formatWithColor('fg-green', '✔'), '', $time, $this->verbose);
        }

        if ($this->bufferExecutionOrder) {
            $this->bufferTestResult($test, $msg);
            $this->flushOutputBuffer();
        } else {
            $this->writeTestResult($msg);

            if ($this->lastTestFailed) {
                $this->bufferTestResult($test, $msg);
            }
        }

        parent::endTest($test, $time);
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->lastTestFailed       = true;
        $this->msg                  = $this->formatTestResultMessage($this->formatWithColor('fg-yellow', '✘'), (string) $t, $time);
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->lastTestFailed       = true;
        $this->msg                  = $this->formatTestResultMessage($this->formatWithColor('fg-yellow', '✘'), (string) $e, $time);
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->lastTestFailed       = true;
        $this->msg                  = $this->formatTestResultMessage($this->formatWithColor('fg-red', '✘'), (string) $e, $time);
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->lastTestFailed       = true;
        $this->msg                  = $this->formatTestResultMessage($this->formatWithColor('fg-yellow', '∅'), (string) $t, $time, true);
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->lastTestFailed       = true;
        $this->msg                  = $this->formatTestResultMessage($this->formatWithColor('fg-yellow', '☢'), (string) $t, $time, true);
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->lastTestFailed       = true;
        $this->msg                  = $this->formatTestResultMessage($this->formatWithColor('fg-yellow', '→'), (string) $t, $time);
    }

    public function bufferTestResult(Test $test, string $msg): void
    {
        $this->outputBuffer[$this->testIndex] = [
            'className' => $this->className,
            'testName'  => TestResultCache::getTestSorterUID($test),
            'message'   => $msg,
            'failed'    => $this->lastTestFailed,
            'verbose'   => $this->lastFlushedTestWasVerbose,
        ];
    }

    public function writeTestResult(string $msg): void
    {
        $msg = $this->formatTestSuiteHeader($this->lastClassName, $this->className, $msg);
        $this->write($msg);
    }

    public function writeProgress(string $progress): void
    {
    }

    public function flush(): void
    {
    }

    public function printResult(TestResult $result): void
    {
        // gets all its information from TestRunner result and runtime env
        $this->printHeader();

        $this->printNonSuccessfulTestsSummary($result->count());

        $this->printFooter($result);
    }

    protected function printHeader(): void
    {
        $this->write("\n" . Timer::resourceUsage() . "\n\n");
    }

    private function flushOutputBuffer(): void
    {
        if ($this->testFlushIndex === $this->testIndex) {
            return;
        }
//        print "## flush fi={$this->testFlushIndex} / i={$this->testIndex}\n";

        if ($this->testFlushIndex > 0) {
            $prevResult = $this->getTestResultByName($this->originalExecutionOrder[$this->testFlushIndex - 1]);
        } else {
            $prevResult = $this->getEmptyTestResult();
        }

        do {
            $flushed = false;
//            print "#  next: {$this->originalExecutionOrder[$this->testFlushIndex]}\n";
            $result = $this->getTestResultByName($this->originalExecutionOrder[$this->testFlushIndex]);

            if (!empty($result)) {
                // Write spacer line for new suite headers and after verbose messages
                if ($prevResult['testName'] !== '' &&
                    ($prevResult['verbose'] === true || $prevResult['className'] !== $result['className'])) {
                    $this->write("\n");
                }

                // Write suite header
                if ($prevResult['className'] !== $result['className']) {
                    $this->write($result['className'] . "\n");
                }

                // Write the test result itself
                $this->write($result['message']);
                $this->testFlushIndex++;
                $prevResult = $result;
                $flushed    = true;
            }
        } while ($flushed && $this->testFlushIndex < $this->testIndex);
    }

    private function getTestResultByName(string $testName): array
    {
        foreach ($this->outputBuffer as $result) {
            if ($result['testName'] === $testName) {
                return $result;
            }
        }

        return [];
    }

    private function formatTestSuiteHeader(?string $lastClassName, string $className, string $msg): string
    {
        if ($lastClassName === null || $className !== $lastClassName) {
            return \sprintf(
                "%s%s\n%s",
                ($this->testFlushIndex > 0) ? "\n" : '',
                $className,
                $msg
            );
        }

        return $msg;
    }

    private function formatTestResultMessage(string $symbol, string $resultMessage, float $time, bool $verbose = false): string
    {
        $additionalInformation = $this->getFormattedAdditionalInformation($resultMessage, $verbose);
        $msg                   = \sprintf(
            " %s %s%s\n%s",
            $symbol,
            $this->testMethod,
            $verbose ? ' ' . $this->getFormattedRuntime($time) : '',
            $additionalInformation
        );

        $this->lastFlushedTestWasVerbose = !empty($additionalInformation);

        return $msg;
    }

    private function getFormattedRuntime(float $time): string
    {
        if ($time > 5) {
            return ($this->colorize)('fg-red', \sprintf('[%.2f ms]', $time * 1000));
        }

        if ($time > 1) {
            return ($this->colorize)('fg-yellow', \sprintf('[%.2f ms]', $time * 1000));
        }

        return \sprintf('[%.2f ms]', $time * 1000);
    }

    private function getFormattedAdditionalInformation(string $resultMessage, bool $verbose): string
    {
        if ($resultMessage === '') {
            return '';
        }

        if ($this->verbose && !$verbose) {
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

        $this->write("Summary of non-successful tests:\n");

        $prevClassName = '';

        foreach ($this->nonSuccessfulTestResults as $testIndex) {
            $result = $this->outputBuffer[$testIndex];

            $msg = $this->formatTestSuiteHeader($prevClassName, $result['className'], $result['message']);
            $msg = \strpos($msg, "\n") === 0 ? $msg : "\n$msg";
            $this->write($msg);
            $prevClassName = $result['className'];
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
}
