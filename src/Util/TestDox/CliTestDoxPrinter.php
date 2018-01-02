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

    public function __construct($out = null, bool $verbose = false, $colors = self::COLOR_DEFAULT, bool $debug = false, $numberOfColumns = 80, bool $reverse = false)
    {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $this->prettifier = new NamePrettifier;
    }

    public function startTest(Test $test): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        $class = \get_class($test);

        if ($test instanceof TestCase) {
            $annotations = $test->getAnnotations();

            if (isset($annotations['class']['testdox'][0])) {
                $className = $annotations['class']['testdox'][0];
            } else {
                $className = $this->prettifier->prettifyTestClass($class);
            }

            if (isset($annotations['method']['testdox'][0])) {
                $testMethod = $annotations['method']['testdox'][0];
            } else {
                $testMethod = $this->prettifier->prettifyTestMethod($test->getName(false));
            }

            $testMethod .= \substr($test->getDataSetAsString(false), 5);
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
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        parent::endTest($test, $time);

        $this->currentTestResult->setRuntime($time);

        $this->write($this->currentTestResult->toString($this->previousTestResult, $this->verbose));

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

    public function writeProgress($progress): void
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
