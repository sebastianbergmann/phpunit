<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\JUnit;

use function assert;
use function is_int;
use function sprintf;
use DOMDocument;
use DOMElement;
use PHPUnit\Event\Assertion\Made;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\OutputPrinted;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class JunitXmlLogger
{
    private DOMDocument $document;

    private DOMElement $root;

    /**
     * @var DOMElement[]
     */
    private array $testSuites = [];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteTests = [0];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteAssertions = [0];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteErrors = [0];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteWarnings = [0];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteFailures = [0];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteSkipped = [0];

    /**
     * @psalm-var array<int,int>
     */
    private array $testSuiteTimes = [0];

    private int $testSuiteLevel = 0;

    private ?DOMElement $currentTestCase = null;

    private int $numberOfAssertions = 0;

    private ?HRTime $time = null;

    private ?string $output = null;

    public function __construct(bool $reportRiskyTests)
    {
        $this->registerSubscribers($reportRiskyTests);
        $this->createDocument();
    }

    public function flush(): string
    {
        return $this->document->saveXML();
    }

    public function testSuiteStarted(Started $event): void
    {
        $testSuite = $this->document->createElement('testsuite');
        $testSuite->setAttribute('name', $event->name());

        if (class_exists($event->name(), false)) {
            try {
                $class = new ReflectionClass($event->name());

                $testSuite->setAttribute('file', $class->getFileName());
            } catch (ReflectionException) {
            }
        }

        if ($this->testSuiteLevel > 0) {
            $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
        } else {
            $this->root->appendChild($testSuite);
        }

        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]          = $testSuite;
        $this->testSuiteTests[$this->testSuiteLevel]      = 0;
        $this->testSuiteAssertions[$this->testSuiteLevel] = 0;
        $this->testSuiteErrors[$this->testSuiteLevel]     = 0;
        $this->testSuiteWarnings[$this->testSuiteLevel]   = 0;
        $this->testSuiteFailures[$this->testSuiteLevel]   = 0;
        $this->testSuiteSkipped[$this->testSuiteLevel]    = 0;
        $this->testSuiteTimes[$this->testSuiteLevel]      = 0;
    }

    public function testSuiteFinished(): void
    {
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'tests',
            (string) $this->testSuiteTests[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'assertions',
            (string) $this->testSuiteAssertions[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'errors',
            (string) $this->testSuiteErrors[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'warnings',
            (string) $this->testSuiteWarnings[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'failures',
            (string) $this->testSuiteFailures[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'skipped',
            (string) $this->testSuiteSkipped[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'time',
            sprintf('%F', $this->testSuiteTimes[$this->testSuiteLevel])
        );

        if ($this->testSuiteLevel > 1) {
            $this->testSuiteTests[$this->testSuiteLevel - 1] += $this->testSuiteTests[$this->testSuiteLevel];
            $this->testSuiteAssertions[$this->testSuiteLevel - 1] += $this->testSuiteAssertions[$this->testSuiteLevel];
            $this->testSuiteErrors[$this->testSuiteLevel - 1] += $this->testSuiteErrors[$this->testSuiteLevel];
            $this->testSuiteWarnings[$this->testSuiteLevel - 1] += $this->testSuiteWarnings[$this->testSuiteLevel];
            $this->testSuiteFailures[$this->testSuiteLevel - 1] += $this->testSuiteFailures[$this->testSuiteLevel];
            $this->testSuiteSkipped[$this->testSuiteLevel - 1] += $this->testSuiteSkipped[$this->testSuiteLevel];
            $this->testSuiteTimes[$this->testSuiteLevel - 1] += $this->testSuiteTimes[$this->testSuiteLevel];
        }

        $this->testSuiteLevel--;
    }

    public function testPrepared(Prepared $event): void
    {
        $testCase = $this->document->createElement('testcase');

        $test = $event->test();

        $testCase->setAttribute('name', $this->name($test));
        $testCase->setAttribute('class', $test->className());
        $testCase->setAttribute('classname', str_replace('\\', '.', $test->className()));
        $testCase->setAttribute('file', $test->file());
        $testCase->setAttribute('line', (string) $test->line());

        $this->currentTestCase    = $testCase;
        $this->numberOfAssertions = 0;
        $this->time               = $event->telemetryInfo()->time();
    }

    public function testPrintedOutput(OutputPrinted $event): void
    {
        $this->output = $event->output();
    }

    public function testFinished(Finished $event): void
    {
        assert($this->time !== null);

        $time = $event->telemetryInfo()->time()->duration($this->time)->asFloat();

        $this->testSuiteAssertions[$this->testSuiteLevel] += $this->numberOfAssertions;

        $this->currentTestCase->setAttribute(
            'assertions',
            (string) $this->numberOfAssertions
        );

        $this->currentTestCase->setAttribute(
            'time',
            sprintf('%F', $time)
        );

        $this->testSuites[$this->testSuiteLevel]->appendChild(
            $this->currentTestCase
        );

        $this->testSuiteTests[$this->testSuiteLevel]++;
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;

        if ($this->output !== null) {
            $systemOut = $this->document->createElement(
                'system-out',
                Xml::prepareString($this->output)
            );

            $this->currentTestCase->appendChild($systemOut);
        }

        $this->currentTestCase    = null;
        $this->numberOfAssertions = 0;
        $this->time               = null;
        $this->output             = null;
    }

    public function testAborted(): void
    {
        $this->handleIncompleteOrSkipped();
    }

    public function testSkipped(): void
    {
        $this->handleIncompleteOrSkipped();
    }

    public function testErrored(Errored $event): void
    {
        $this->handleFault($event->test(), $event->throwable(), 'error');

        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    public function testFailed(Failed $event): void
    {
        $this->handleFault($event->test(), $event->throwable(), 'failure');

        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }

    public function testPassed(Passed $event): void
    {
    }

    public function testPassedWithWarning(PassedWithWarning $event): void
    {
        $this->handleFault($event->test(), $event->throwable(), 'warning');

        $this->testSuiteWarnings[$this->testSuiteLevel]++;
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        $this->handleFault($event->test(), $event->throwable(), 'error');

        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    public function assertionMade(Made $event): void
    {
        $this->numberOfAssertions += $event->constraint()->count();
    }

    private function registerSubscribers(bool $reportRiskyTests): void
    {
        Facade::registerSubscriber(new TestSuiteStartedSubscriber($this));
        Facade::registerSubscriber(new TestSuiteFinishedSubscriber($this));
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
        Facade::registerSubscriber(new TestPrintedOutputSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestPassedSubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestAbortedSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
        Facade::registerSubscriber(new AssertionMadeSubscriber($this));

        if ($reportRiskyTests) {
            Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        }
    }

    private function createDocument(): void
    {
        $this->document               = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);
    }

    private function handleFault(Test $test, Throwable $throwable, string $type): void
    {
        /*
         * @todo Figure out why this safeguard is required
         */
        if ($this->currentTestCase === null) {
            return;
        }

        $buffer = $this->testAsString($test);

        $buffer .= trim(
            $throwable->description() . PHP_EOL .
            $throwable->stackTrace()
        );

        $fault = $this->document->createElement(
            $type,
            Xml::prepareString($buffer)
        );

        $fault->setAttribute('type', $throwable->className());

        $this->currentTestCase->appendChild($fault);
    }

    private function handleIncompleteOrSkipped(): void
    {
        /*
         * @todo Figure out why this safeguard is required
         */
        if ($this->currentTestCase === null) {
            return;
        }

        $skipped = $this->document->createElement('skipped');

        $this->currentTestCase->appendChild($skipped);

        $this->testSuiteSkipped[$this->testSuiteLevel]++;
    }

    private function testAsString(Test $test): string
    {
        return sprintf(
            '%s::%s%s' . \PHP_EOL,
            $test->className(),
            $test->methodName(),
            $test->dataSet()
        );
    }

    private function name(Test $test): string
    {
        if (!$test->usesProvidedData()) {
            return $test->methodName();
        }

        $dataSetName = $test->dataSetName();

        if (is_int($dataSetName)) {
            return sprintf(
                '%s with data set #%d',
                $test->methodName(),
                $dataSetName
            );
        }

        return sprintf(
            '%s with data set "%s"',
            $test->methodName(),
            $dataSetName
        );
    }
}
