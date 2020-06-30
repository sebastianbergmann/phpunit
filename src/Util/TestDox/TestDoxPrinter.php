<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use const PHP_EOL;
use function array_map;
use function get_class;
use function implode;
use function preg_split;
use function trim;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\DefaultResultPrinter;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class TestDoxPrinter extends DefaultResultPrinter
{
    /**
     * @var bool
     */
    protected $currentSuiteIsDataProvider = false;

    /**
     * @var NamePrettifier
     */
    protected $prettifier;

    /**
     * @var int The number of test results received from the TestRunner
     */
    protected $resultCount = 0;

    /**
     * @var int The number of test results already sent to the output
     */
    protected $resultFlushCount = 0;

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

    /**
     * @var int
     */
    protected $spinState = 0;

    /**
     * @var bool
     */
    protected $showProgress = true;

    /**
     * @var int
     */
    private $unnamedIndex = 0;

    /**
     * @var string
     */
    private $currentSuiteName = '';

    /**
     * @var array<string>
     */
    private $suiteStack;

    /**
     * @var array
     */
    private $prevResult;

    /**
     * @var array
     */
    private $completedTestSuites;

    /**
     * @param null|resource|string $out
     * @param int|string           $numberOfColumns
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($out = null, bool $verbose = false, string $colors = self::COLOR_DEFAULT, bool $debug = false, $numberOfColumns = 80, bool $reverse = false)
    {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

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

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase && !$test instanceof PhptTestCase && !$test instanceof TestSuite) {
            return;
        }

        if ($this->testHasPassed()) {
            $this->registerTestResult($test, null, BaseTestRunner::STATUS_PASSED, $time, false);
        }

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            $this->resultCount++;
        }

        parent::endTest($test, $time);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_ERROR, $time, true);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->registerTestResult($test, $e, BaseTestRunner::STATUS_WARNING, $time, true);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->registerTestResult($test, $e, BaseTestRunner::STATUS_FAILURE, $time, true);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_INCOMPLETE, $time, false);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_RISKY, $time, false);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        $this->registerTestResult($test, $t, BaseTestRunner::STATUS_SKIPPED, $time, false);
    }

    public function writeProgress(string $progress): void
    {
        $this->flushOutputBuffer();
    }

    public function flush(): void
    {
        $this->flushOutputBuffer(true);
    }

    public function startTestSuite(TestSuite $suite): void
    {
        parent::startTestSuite($suite); // TODO: Change the autogenerated stub

        $this->currentSuiteName           = $this->safeSuiteName($suite);
        $this->currentSuiteIsDataProvider = $suite instanceof DataProviderTestSuite;
        $this->suiteStack[]               = $this->currentSuiteName;
    }

    public function endTestSuite(TestSuite $suite): void
    {
        parent::endTestSuite($suite); // TODO: Change the autogenerated stub

        $this->currentSuiteName                      = '';
        $this->currentSuiteIsDataProvider            = false;
        $finishedSuiteId                             = array_pop($this->suiteStack);
        $this->completedTestSuites[$finishedSuiteId] = true;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function registerTestResult(Test $test, ?Throwable $t, int $status, float $time, bool $verbose): void
    {
        $testName = $test instanceof Reorderable ? $test->sortId() : $test->getName();

        $result = [
            'className'    => $this->formatClassName($test),
            'testName'     => $testName,
            'testMethod'   => $this->formatTestName($test),
            'message'      => '',
            'status'       => $status,
            'time'         => $time,
            'verbose'      => $verbose,
            'suite'        => $this->currentSuiteName,
            'dataprovider' => $this->currentSuiteIsDataProvider,
        ];

        if ($t !== null) {
            $result['message'] = $this->formatTestResultMessage($t, $result);
        }

        $this->testResults[$this->resultCount] = $result;
        $this->testNameResultIndex[$testName]  = $this->resultCount;
    }

    protected function formatTestName(Test $test): string
    {
        return method_exists($test, 'getName') ? $test->getName() : '';
    }

    protected function formatClassName(Test $test): string
    {
        return get_class($test);
    }

    protected function testHasPassed(): bool
    {
        if (!isset($this->testResults[$this->resultCount]['status'])) {
            return true;
        }

        if ($this->testResults[$this->resultCount]['status'] === BaseTestRunner::STATUS_PASSED) {
            return true;
        }

        return false;
    }

    protected function flushOutputBuffer(bool $forceFlush = false): void
    {
        if ($this->resultFlushCount === $this->resultCount) {
            return;
        }

        if ($this->resultFlushCount === 0) {
            $this->prevResult = $this->getEmptyTestResult();
        }

        // Force flush: dump any remaining results straight to the output stream
        if ($forceFlush) {
            $this->hideSpinner();

            for (;$this->resultFlushCount < $this->resultCount; $this->resultFlushCount++) {
                $this->writeTestResult($this->prevResult, $this->testResults[$this->resultFlushCount++]);
            }

            return;
        }

        // Unbuffered: directly write test results in the order they are registered
        if (!$this->enableOutputBuffer) {
            $this->writeTestResult($this->prevResult, $this->testResults[$this->resultFlushCount]);
            $this->resultFlushCount++;

            return;
        }

        // Buffered output: match original test load order as close as possible
        do {
            if (isset($this->originalExecutionOrder[$this->resultFlushCount])) {
                $result = $this->getTestResultByName($this->originalExecutionOrder[$this->resultFlushCount]);

                if (empty($result)) {
                    $this->showSpinner();

                    return;
                }
            } else {
                // Edge case for exceptions in tearDownAfterClass
                // This test(name) cannot found in original execution order,
                // flush result to output stream as part of the current suite
                $result = $this->testResults[$this->resultFlushCount];
            }

            $this->hideSpinner();
            $this->writeTestResult($this->prevResult, $result);
            $this->resultFlushCount++;
            $this->prevResult = $result;
        } while (!empty($result) && $this->resultFlushCount < $this->resultCount);
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

    protected function formatThrowable(Throwable $t, ?int $status = null): string
    {
        $message = trim(\PHPUnit\Framework\TestFailure::exceptionToString($t));

        if ($message) {
            $message .= PHP_EOL . PHP_EOL . $this->formatStacktrace($t);
        } else {
            $message = $this->formatStacktrace($t);
        }

        return $message;
    }

    protected function formatStacktrace(Throwable $t): string
    {
        return \PHPUnit\Util\Filter::getFilteredStacktrace($t);
    }

    protected function formatTestResultMessage(Throwable $t, array $result, string $prefix = '│'): string
    {
        $message = $this->formatThrowable($t, $result['status']);

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
                static function (string $text) use ($prefix) {
                    return '   ' . $prefix . ($text ? ' ' . $text : '');
                },
                preg_split('/\r\n|\r|\n/', $message)
            )
        );
    }

    protected function safeSuiteName(TestSuite $suite): string
    {
        $id = $suite->sortId();

        if (trim($id) === '') {
            $id = 'suite_' . $this->unnamedIndex++;
        }

        return $id;
    }
}
