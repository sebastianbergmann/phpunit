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
     * @var array
     */
    protected $prevResult;

    /**
     * @var string
     */
    protected $currentSuiteName = '';

    /**
     * @var array<string>
     */
    protected $testSuiteStack = [];

    /**
     * @var array
     */
    protected $completedTestSuites = [];

    /**
     * @var array
     */
    private $unflushedResults = [];

    /**
     * @var null|array
     */
    private $currentTestResult;

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

    public function setEnableOutputBuffer(bool $enabled = true): void
    {
        $this->enableOutputBuffer = $enabled;
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
        if ($this->currentTestResult === null) {
            return;
        }

        if ($this->currentTestNotMarkedDefective()) {
            $this->registerTestResult($test, null, BaseTestRunner::STATUS_PASSED, $time, false);
        }

        $this->unflushedResults[] = $this->currentTestResult;
        $this->currentTestResult  = null;
        $this->resultCount++;

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

    public function writeProgress(string $progress = ''): void
    {
        $this->flushOutputBuffer();
    }

    public function flush(): void
    {
        $this->flushOutputBuffer(true);
    }

    public function startTest(Test $test): void
    {
        parent::startTest($test);

        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return;
        }

        $result = [
            'className'    => $this->formatClassName($test),
            'testName'     => $test->sortId(),
            'testMethod'   => $this->formatTestName($test),
            'message'      => '',
            'status'       => BaseTestRunner::STATUS_UNKNOWN,
            'suite'        => $this->currentSuiteName,
            'dataProvider' => $this->currentSuiteIsDataProvider,
            'index'        => -1,
        ];

        if ($this->enableOutputBuffer) {
            $result['index'] = $this->getOriginalIndexForTest($result['testName']);
        }

        $this->currentTestResult = $result;
    }

    public function startTestSuite(TestSuite $suite): void
    {
        parent::startTestSuite($suite);

        $this->currentSuiteName           = $suite->sortId();
        $this->currentSuiteIsDataProvider = $suite instanceof DataProviderTestSuite;
        $this->testSuiteStack[]           = $this->currentSuiteName;
    }

    public function endTestSuite(TestSuite $suite): void
    {
        parent::endTestSuite($suite);

        $this->currentSuiteName                      = '';
        $this->currentSuiteIsDataProvider            = false;
        $finishedSuiteId                             = array_pop($this->testSuiteStack);
        $this->completedTestSuites[$finishedSuiteId] = true;

        $this->flushOutputBuffer();
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function registerTestResult(Test $test, ?Throwable $t, int $status, float $time, bool $verbose): void
    {
        $this->currentTestResult['status']  = $status;
        $this->currentTestResult['time']    = $time;
        $this->currentTestResult['verbose'] = $verbose;

        if ($t !== null) {
            $this->currentTestResult['message'] = $this->formatTestResultMessage($t, $this->currentTestResult);
        }

        $this->testResults[$this->resultCount] = $this->currentTestResult;
    }

    protected function getOriginalIndexForTest(string $testName): ?int
    {
        $index = array_search($testName, $this->originalExecutionOrder, true);

        return $index !== false ? $index : null;
    }

    protected function formatTestName(Test $test): string
    {
        return method_exists($test, 'getName') ? $test->getName() : '';
    }

    protected function formatClassName(Test $test): string
    {
        return get_class($test);
    }

    protected function currentTestNotMarkedDefective(): bool
    {
        if ($this->currentTestResult !== null && (
            $this->currentTestResult['status'] === BaseTestRunner::STATUS_PASSED ||
            $this->currentTestResult['status'] === BaseTestRunner::STATUS_UNKNOWN
        )) {
            return true;
        }

        return false;
    }

    protected function flushOutputBuffer(bool $forceFlush = false): void
    {
        // Nothing to do
        if ($this->unflushedResults === []) {
            return;
        }

        // When the first result comes in, switch off the buffer when it isn't needed
        if ($this->noPreviousOutput() && $this->originalExecutionOrder === []) {
            $this->setEnableOutputBuffer(false);
        }

        // Unbuffered or force flush: dump any remaining results straight to the output stream
        if ($forceFlush || !$this->enableOutputBuffer) {
            $this->forceFlush();

            return;
        }

        $this->flushOutputBufferInOrder();
    }

    protected function forceFlush(): void
    {
        $this->hideSpinner();

        foreach ($this->unflushedResults as $result) {
            $this->writeSingleTestResult($result);
        }

        $this->unflushedResults = [];
    }

    protected function flushOutputBufferInOrder(?string $suiteName = null, ?string $dataProviderName = null): void
    {
        $flushed = false;

        do {
            $nextResult = $this->popNextFlushableResultFromBuffer();

            if (!$nextResult) {
                break;
            }

            $this->hideSpinner();
            $this->writeSingleTestResult($nextResult);
            $flushed = true;
        } while ($nextResult && $this->unflushedResults !== []);

        if (!$flushed) {
            $this->showSpinner();
        }
    }

    protected function popNextFlushableResultFromBuffer(): ?array
    {
        $prevIndex = $this->lastFlushedIndex();

        // Look for the result for the next test in the original order
        // Start with the most recently added result
        for ($i = count($this->unflushedResults) - 1; $i >= 0; $i--) {
            if ($this->unflushedResults[$i]['index'] === $prevIndex + 1) {
                [$nextResult] = array_splice($this->unflushedResults, $i, 1);

                return $nextResult;
            }
        }

        // Look for any out-of-order test results of completed TestSuites
        // For now this is only triggered by tearDownAfterClass errors
        for ($i = count($this->unflushedResults) - 1; $i >= 0; $i--) {
            if (array_search($this->unflushedResults[$i]['suite'], $this->completedTestSuites, true)) {
                [$nextResult] = array_splice($this->unflushedResults, $i, 1);

                return $nextResult;
            }
        }

        return null;
    }

    protected function lastFlushedIndex(): int
    {
        if (isset($this->prevResult['index'])) {
            return $this->prevResult['index'];
        }

        return -1;
    }

    protected function noPreviousOutput(): bool
    {
        return !isset($this->prevResult);
    }

    protected function showSpinner(): void
    {
        if (!$this->showProgress || !$this->enableOutputBuffer) {
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
        if (!$this->showProgress || !$this->enableOutputBuffer) {
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

    protected function writeSingleTestResult(array $result): void
    {
        $this->write(sprintf(
            '%s::%s%s' . PHP_EOL,
            $result['className'],
            $result['testMethod'],
            $result['message'] !== '' ? "\n" . $result['message'] : ''
        ));

        $this->prevResult = $result;
    }

    protected function getEmptyTestResult(): array
    {
        return [
            'className' => '',
            'testName'  => '',
            'message'   => '',
            'status'    => '',
            'verbose'   => '',
            'index'     => -1,
        ];
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
}
