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
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Util\TestDox\TestResult as TestDoxTestResult;
use SebastianBergmann\Timer\Timer;

/**
 * This printer is for CLI output only. For the classes that output to file, html and xml,
 * please refer to the PHPUnit\Util\TestDox namespace
 */
class CliTestDoxPrinter extends ResultPrinter
{
    /**
     * @var TestDoxTestResult
     */
    private $currentTestResult;

    /**
     * @var TestDoxTestResult
     */
    private $previousTestResult;

    /**
     * @var TestDoxTestResult[]
     */
    private $nonSuccessfulTestResults = [];

    /**
     * @var NamePrettifier
     */
    private $prettifier;

    /**
     * @var int The number of test results received from the TestRunner
     */
    private $testCount = 0;

    /**
     * @var int The number of test results already sent to the output
     */
    private $testFlushCount = 0;

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

        $class = \get_class($test);

        if ($test instanceof TestCase) {
            $className  = $this->prettifier->prettifyTestClass($class);
            $testMethod = $this->prettifier->prettifyTestCase($test);
        } elseif ($test instanceof TestSuite) {
            $className  = $test->getName();
            $testMethod = \sprintf(
                'Error bootstapping suite (most likely in %s::setUpBeforeClass)',
                $test->getName()
            );
        } elseif ($test instanceof PhptTestCase) {
            $className  = $class;
            $testMethod = $test->getName();
        }

        $this->currentTestResult = new TestDoxTestResult(
            function (string $color, string $buffer) {
                return $this->formatWithColor($color, $buffer);
            },
            $className,
            $testMethod
        );

        parent::startTest($test);
    }

    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase && !$test instanceof TestSuite) {
            return;
        }

        parent::endTest($test, $time);

        $this->currentTestResult->setRuntime($time);

        $testName = '';
        if ($test instanceof PhptTestCase) {
            $testName = $test->getName();
            $this->testCount++;
        } elseif ($test instanceof TestCase) {
            $testName = $test->getName(true);

            if (\strpos($testName, '::') === false) {
                $testName = \get_class($test) . '::' . $testName;
            }
            $this->testCount++;
        }

        $this->writeOriginalExecutionOrder($testName, $this->currentTestResult->toString($this->previousTestResult, $this->verbose));

        $this->previousTestResult = $this->currentTestResult;

        if (!$this->currentTestResult->isTestSuccessful()) {
            $this->nonSuccessfulTestResults[] = $this->currentTestResult;
        }
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->formatWithColor('fg-yellow', '✘'),
            (string) $t
        );
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->currentTestResult->fail(
            $this->formatWithColor('fg-yellow', '✘'),
            (string) $e
        );
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->currentTestResult->fail(
            $this->formatWithColor('fg-red', '✘'),
            (string) $e
        );
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->formatWithColor('fg-yellow', '∅'),
            (string) $t,
            true
        );
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->formatWithColor('fg-yellow', '☢'),
            (string) $t,
            true
        );
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->currentTestResult->fail(
            $this->formatWithColor('fg-yellow', '→'),
            (string) $t,
            true
        );
    }

    public function writeOriginalExecutionOrder(string $testname, string $msg): void
    {
        if (!$this->bufferExecutionOrder) {
            $this->write($msg);

            return;
        }

        if ($testname == $this->originalExecutionOrder[$this->testFlushCount]) {
            $this->write($msg);
            $this->testFlushCount++;

            while ($this->testFlushCount < $this->testCount && isset($this->outputBuffer[$this->originalExecutionOrder[$this->testFlushCount]])) {
//                $this->write("** flushing {$this->originalExecutionOrder[$this->testFlushCount]}\n");
                foreach($this->outputBuffer[$this->originalExecutionOrder[$this->testFlushCount++]] as $line) {
                    $this->write($line);
                }
            }
        } else {
//            parent::write("** buffering $testname\n");
            $this->outputBuffer[$testname][] = $msg;
        }
    }

    public function write(string $msg): void
    {
        parent::write($msg);
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

    private function printNonSuccessfulTestsSummary(int $numberOfExecutedTests): void
    {
        $numberOfNonSuccessfulTests = \count($this->nonSuccessfulTestResults);

        if ($numberOfNonSuccessfulTests === 0) {
            return;
        }

        if (($numberOfNonSuccessfulTests / $numberOfExecutedTests) >= 0.7) {
            return;
        }

        $this->write("Summary of non-successful tests:\n\n");

        $previousTestResult = null;

        foreach ($this->nonSuccessfulTestResults as $testResult) {
            $this->write($testResult->toString($previousTestResult, $this->verbose));

            $previousTestResult = $testResult;
        }
    }
}
