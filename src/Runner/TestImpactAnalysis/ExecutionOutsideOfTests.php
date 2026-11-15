<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\FilterProcessor;
use SebastianBergmann\CodeCoverage\StaticAnalysis\FileAnalyser;

/**
 * Records what is executed outside of the tests, and which tests it is what
 * they depend on.
 *
 * What a test executes is recorded while the test runs, and that is not all
 * a test depends on. The bootstrap script, loading the tests, a data provider,
 * and the methods that are called before the first test of a test class is
 * run all execute code before the test starts, and what that code does is
 * what the test finds when it starts.
 *
 * The code coverage driver is therefore kept running from before the
 * bootstrap script is loaded until what was recorded is persisted, and only
 * paused while a test runs: what a test executes is recorded for that test,
 * and code coverage is never collected for two purposes at the same time.
 * What is executed outside of the tests is recorded for the tests it can
 * affect:
 *
 * - what a data provider executes, for the tests that are made from the data
 *   it provides,
 * - what is executed while the tests of a test class are run, the methods
 *   that are called before the first and after the last of them included,
 *   for the tests of that test class,
 * - and everything else, for every test.
 *
 * What is executed while a test runs is left to the test, even when it is a
 * data provider of a test that the test builds, or a method that is called
 * before the first test of a test class whose tests the test tries to run:
 * a test cannot run another test, but it gets that far.
 *
 * What was collected is filtered the way what a test executed is filtered
 * before it is recorded: only the lines that are executable count. Loading a
 * file that declares a class executes no line of the class, although the code
 * coverage driver reports the end of the file as executed, and a class that is
 * merely loaded while PHPUnit is bootstrapped is not something every test
 * depends on.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExecutionOutsideOfTests
{
    /**
     * @phpstan-ignore property.internalClass
     */
    private readonly Driver $driver;
    private readonly Filter $filter;

    /**
     * @phpstan-ignore property.internalClass
     */
    private readonly FileAnalyser $analyser;
    private readonly bool $useAnnotationsForIgnoringCode;
    private bool $collecting = false;
    private bool $paused     = false;
    private bool $stopped    = false;

    /**
     * The tests of one test class are not run while those of another are:
     * a test cannot run another test, and what is executed while a test runs
     * is left to the test.
     *
     * @var ?non-empty-string
     */
    private ?string $testClass = null;

    /**
     * @var ?non-empty-string
     */
    private ?string $dataProvider = null;

    /**
     * @var array<non-empty-string, true>
     */
    private array $executedOutsideOfTests = [];

    /**
     * @var array<non-empty-string, array<non-empty-string, true>>
     */
    private array $executedForTestClass = [];

    /**
     * @var array<non-empty-string, array<non-empty-string, true>>
     */
    private array $executedForDataProvider = [];

    /**
     * @var array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    private array $recordedTests = [];

    /**
     * @phpstan-ignore parameter.internalClass, parameter.internalClass
     */
    public function __construct(Driver $driver, Filter $filter, FileAnalyser $analyser, bool $useAnnotationsForIgnoringCode)
    {
        $this->driver                        = $driver;
        $this->filter                        = $filter;
        $this->analyser                      = $analyser;
        $this->useAnnotationsForIgnoringCode = $useAnnotationsForIgnoringCode;
    }

    public function start(): void
    {
        if ($this->collecting || $this->paused || $this->stopped) {
            return;
        }

        $this->startCollecting();
    }

    /**
     * A test starts, and what it executes is recorded for it, or an example
     * of a property is run for which nothing is recorded: one that is run
     * while the property is shrunk, or to replay its minimal counterexample.
     */
    public function pause(): void
    {
        if (!$this->collecting) {
            return;
        }

        $this->collect();

        $this->paused = true;
    }

    /**
     * The test, or the example of a property, has finished.
     */
    public function resume(): void
    {
        if (!$this->paused) {
            return;
        }

        $this->paused = false;

        $this->startCollecting();
    }

    /**
     * @param non-empty-string $className
     */
    public function enterTestClass(string $className): void
    {
        if (!$this->collecting) {
            return;
        }

        $this->collect();

        $this->testClass = $className;

        $this->startCollecting();
    }

    public function leaveTestClass(): void
    {
        if (!$this->collecting) {
            return;
        }

        $this->collect();

        $this->testClass = null;

        $this->startCollecting();
    }

    /**
     * @param non-empty-string $className
     * @param non-empty-string $methodName the test method the data is provided for
     */
    public function enterDataProvider(string $className, string $methodName): void
    {
        if (!$this->collecting) {
            return;
        }

        $this->collect();

        $this->dataProvider = $className . '::' . $methodName;

        $this->startCollecting();
    }

    public function leaveDataProvider(): void
    {
        if (!$this->collecting) {
            return;
        }

        $this->collect();

        $this->dataProvider = null;

        $this->startCollecting();
    }

    /**
     * What a test executed was recorded under the name of the test, and what
     * was executed for its test class and for its data provider is added to
     * it once everything has been recorded: the methods that are called after
     * the last test of a test class is run are called after the test.
     *
     * @param non-empty-string $test
     * @param non-empty-string $className
     * @param non-empty-string $methodName
     */
    public function testWasRecorded(string $test, string $className, string $methodName): void
    {
        $this->recordedTests[$test] = [$className, $methodName];
    }

    /**
     * What a worker process of a parallel test run executed outside of the
     * tests it ran is what the tests can be affected by as well: the
     * bootstrap script may do in a worker process what it does not do in
     * the main process.
     *
     * @param list<non-empty-string> $files
     */
    public function recordExecutedOutsideOfTestsInWorker(array $files): void
    {
        foreach ($files as $file) {
            $this->executedOutsideOfTests[$file] = true;
        }
    }

    /**
     * Hands over what was recorded since it was last handed over: a worker
     * process of a parallel test run ships what it recorded with the result
     * of the tests of every test class it ran, and what was shipped is not
     * shipped again with the tests of the next test class.
     *
     * What was executed for the test class and for the data providers of the
     * tests is added to what the tests executed before it is handed over:
     * the tests of a test class are run by one worker process, which has
     * therefore called the methods that are called after the last of them.
     *
     * @return array{tests: array<non-empty-string, array{className: non-empty-string, methodName: non-empty-string, files: list<non-empty-string>}>, executedOutsideOfTests: list<non-empty-string>}
     */
    public function handOver(TestImpactData $data): array
    {
        $collecting = $this->collecting;

        if ($collecting) {
            $this->collect();
        }

        $this->addTo($data);

        $tests = [];

        foreach ($data->recorded() as $test => $files) {
            if (!isset($this->recordedTests[$test])) {
                continue;
            }

            [$className, $methodName] = $this->recordedTests[$test];

            $tests[$test] = [
                'className'  => $className,
                'methodName' => $methodName,
                'files'      => $files,
            ];
        }

        $executedOutsideOfTests = array_keys($this->executedOutsideOfTests);

        $this->executedOutsideOfTests  = [];
        $this->executedForTestClass    = [];
        $this->executedForDataProvider = [];
        $this->recordedTests           = [];

        if ($collecting) {
            $this->startCollecting();
        }

        return [
            'tests'                  => $tests,
            'executedOutsideOfTests' => $executedOutsideOfTests,
        ];
    }

    public function stop(): void
    {
        if ($this->collecting) {
            $this->collect();
        }

        $this->paused  = false;
        $this->stopped = true;
    }

    /**
     * Adds what was executed outside of the tests to what the tests executed.
     */
    public function addTo(TestImpactData $data): void
    {
        $data->recordExecutedOutsideOfTests(array_keys($this->executedOutsideOfTests));

        foreach ($data->recorded() as $test => $files) {
            if (!isset($this->recordedTests[$test])) {
                continue;
            }

            [$className, $methodName] = $this->recordedTests[$test];

            $additionalFiles = [];

            if (isset($this->executedForTestClass[$className])) {
                $additionalFiles = array_keys($this->executedForTestClass[$className]);
            }

            if (isset($this->executedForDataProvider[$className . '::' . $methodName])) {
                $additionalFiles = array_merge(
                    $additionalFiles,
                    array_keys($this->executedForDataProvider[$className . '::' . $methodName]),
                );
            }

            if ($additionalFiles === []) {
                continue;
            }

            $data->record($test, array_values(array_unique(array_merge($files, $additionalFiles))));
        }
    }

    private function startCollecting(): void
    {
        /** @phpstan-ignore method.internalClass */
        $this->driver->start();

        $this->collecting = true;
    }

    private function collect(): void
    {
        /** @phpstan-ignore method.internalClass */
        $data = $this->driver->stop();

        $this->collecting = false;

        /** @phpstan-ignore new.internalClass */
        $filterProcessor = new FilterProcessor;

        /** @phpstan-ignore method.internalClass */
        $filterProcessor->applyFilter($data, $this->filter);

        /** @phpstan-ignore method.internalClass */
        $filterProcessor->applyExecutableLinesFilter($data, $this->filter, $this->analyser);

        if ($this->useAnnotationsForIgnoringCode) {
            /** @phpstan-ignore method.internalClass */
            $filterProcessor->applyIgnoredLinesFilter($data, $this->filter, $this->analyser);
        }

        foreach (ExecutedFiles::in($data) as $file) {
            if ($this->dataProvider !== null) {
                $this->executedForDataProvider[$this->dataProvider][$file] = true;

                continue;
            }

            if ($this->testClass !== null) {
                $this->executedForTestClass[$this->testClass][$file] = true;

                continue;
            }

            $this->executedOutsideOfTests[$file] = true;
        }
    }
}
