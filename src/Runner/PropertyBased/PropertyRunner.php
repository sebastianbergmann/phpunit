<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PropertyBased;

use function array_is_list;
use function count;
use function getmypid;
use function is_int;
use function is_string;
use function register_shutdown_function;
use function sprintf;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\TestRunner\PhpunitWarningTriggered;
use PHPUnit\Exception;
use PHPUnit\Framework\PropertyBased\ExampleResult;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\TestIndex\NullTestFileSkipper;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\CliArguments\XmlConfigurationFileFinder;
use PHPUnit\TextUI\Configuration\BootstrapLoader;
use PHPUnit\TextUI\Configuration\PhpHandler;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\TestSuiteBuilder;
use PHPUnit\TextUI\ErrorHandlerConfigurator;
use PHPUnit\TextUI\TestSuiteFilterProcessor;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\Util\DifferBuilder;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\ChoiceSequence\SpanKind;
use SebastianBergmann\Invoker\Invoker;
use Throwable;

/**
 * Runs the examples of the properties of a test suite one at a time, for
 * tools that drive property-based tests themselves, a fuzzer that is guided
 * by code coverage, for instance.
 *
 * Everything that is passed to this API and returned by it is data: strings,
 * integers, null, and arrays of these. No object of a class of one of
 * PHPUnit's dependencies crosses it, so it is used in the same way whether
 * PHPUnit is installed with Composer or used from its PHAR, in which the
 * names of these classes are prefixed.
 *
 * PHPUnit is booted once per process. A boot that fails because the
 * configuration file cannot be found or read changes nothing and can be
 * retried. The configuration file and its bootstrap script are loaded as for
 * a test run, but nothing is printed, no code coverage is collected, nothing
 * is logged, and no extensions are loaded. As nothing is printed, a warning
 * that PHPUnit triggers while it is booted, about a configuration file that
 * did not pass validation, for instance, is raised as an exception.
 *
 * As in a test run, the random number generators whose state is global to
 * the process, the one that mt_rand() uses, for instance, are seeded before
 * each example when a random seed is passed while PHPUnit is booted, or when
 * the configuration file configures a random execution order, for which
 * PHPUnit chooses a random seed itself. The seed for an example is derived
 * from the random seed and the property, so that the example gets the same
 * random numbers in every process that is booted with the same random seed.
 * randomSeed() tells which random seed is used.
 *
 * An example is run with its full lifecycle, from the hook methods that are
 * called before the property to the ones that are called after it, and with
 * the time limit that is passed, which covers generating its arguments as
 * well as running it. Its arguments are generated from choices as when
 * PHPUnit checks the property, and it draws values with draw() from the same
 * choices. It is neither shrunk nor reported. The issues that it triggers are
 * handled as in a test run, but they are not reported either.
 *
 * As in a test run, one test class is prepared at a time. The hook methods of
 * a test class that are called before its first test are called before the
 * first example of one of its properties is run, and the ones that are called
 * after its last test are called before an example of a property of another
 * test class is run, or when the process ends.
 * When one of the former raises an exception, every example of a property of
 * the test class is aborted with that exception, and when one of them skips
 * the tests of the test class, every example is skipped.
 *
 * The result of an example tells how it ended, how it falsified the
 * property, and which choices it consumed, with their spans and the maxima
 * of the choices. The kind of a span, when it has one, is the name of the
 * case of SpanKind of sebastian/choice-sequence in lowercase: 'bytes',
 * 'characters', or 'element'. On request, the result also has the arguments
 * of the example, the values that it drew with draw(), and the steps of its
 * state machine, exported as PHPUnit reports them for a counterexample. They
 * are null otherwise, and when no arguments were generated for the example.
 *
 * An example that fails or errors without checking the property is aborted
 * rather than falsifying it: when a generator raises an exception while its
 * arguments are generated, when it ends before its test method is called, in
 * setUp(), for instance, or when its state machine cannot be run. When PHPUnit
 * checks the property, such an example is the error of the property, which is
 * reported without being shrunk. The failure of an aborted example tells how
 * it ended.
 *
 * @phpstan-type Result array{outcome: 'aborted'|'errored'|'failed'|'incomplete'|'passed'|'rejected'|'skipped'|'timeout', failure: ?array{kind: 'description'|'throwable'|'timeout', signature: non-empty-string, class: ?class-string, location: ?non-empty-string, description: non-empty-string, message: string}, choices: list<non-negative-int>, spans: list<array{start: non-negative-int, end: non-negative-int, label: string, parent: ?non-negative-int, kind: null|'bytes'|'characters'|'element'}>, maxima: list<?non-negative-int>, arguments: ?array<non-empty-string, string>, drawnValues: ?list<array{label: ?non-empty-string, value: string}>, steps: ?list<non-empty-string>}
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class PropertyRunner
{
    private static bool $booted = false;

    /**
     * @var array<non-empty-string, array{property: PropertyTestSuite, testClass: ?TestSuite}>
     */
    private readonly array $properties;

    /**
     * @var ?positive-int
     */
    private readonly ?int $randomSeed;

    /**
     * The test class whose hook methods that are called before its first test
     * were called last, and what calling them resulted in: null when they
     * were called, the message why its tests are skipped, or the throwable
     * that one of them raised.
     *
     * @var ?array{testClass: TestSuite, outcome: null|non-empty-string|Throwable}
     */
    private ?array $preparedTestClass = null;

    /**
     * Loads the configuration file and its bootstrap script and builds the
     * test suite, with only the tests that match the filter, as with the
     * --filter option of the test runner.
     *
     * @param ?non-empty-string $configurationFile the configuration file, null to look for one in the working directory as the test runner does
     * @param ?non-empty-string $filter            the filter, null for all tests
     * @param ?positive-int     $randomSeed        the random seed with which the random number generators are seeded before each example, as with the --random-seed option of the test runner, null to seed them only when the configuration file configures a random execution order
     *
     * @throws AlreadyBootedException
     * @throws ConfigurationFileNotFoundException    when the configuration file that is passed is a directory without one
     * @throws Exception                             when the configuration file, its bootstrap script, or the test suite cannot be loaded
     * @throws InvalidArgumentException
     * @throws TimeLimitCannotBeEnforcedException
     * @throws WarningTriggeredWhileBootingException when PHPUnit triggered a warning while the configuration file was loaded or the test suite was built
     */
    public static function boot(?string $configurationFile = null, ?string $filter = null, ?int $randomSeed = null): self
    {
        if (self::$booted) {
            throw new AlreadyBootedException;
        }

        if ($randomSeed !== null && $randomSeed < 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'The random seed must be a positive integer, %d given',
                    $randomSeed,
                ),
            );
        }

        if (!new Invoker()->canInvokeWithTimeout()) {
            throw new TimeLimitCannotBeEnforcedException;
        }

        $facade = EventFacade::instance();

        // the events that are emitted while PHPUnit is booted are collected
        // rather than dispatched, so that the warnings among them, about a
        // configuration file that did not pass validation, for instance, are
        // raised instead of being lost
        $facade->startCollectingEvents();

        try {
            $testSuite = self::testSuite($configurationFile, $filter, $randomSeed);
        } finally {
            $events = $facade->stopCollectingEvents();
        }

        $warnings = [];

        foreach ($events as $event) {
            if ($event instanceof PhpunitWarningTriggered) {
                $warnings[] = $event->message();
            }
        }

        if ($warnings !== []) {
            throw new WarningTriggeredWhileBootingException($warnings);
        }

        $configuration = Registry::get();

        // the random seed is null when the random number generators are not
        // seeded, as the one that PHPUnit chose then has no effect on the
        // examples
        $randomSeed = null;

        if ($configuration->seedRandomNumberGenerators()) {
            $randomSeed = $configuration->randomSeed();
        }

        return new self(self::propertiesIn($testSuite, null), $randomSeed);
    }

    /**
     * @param array<non-empty-string, array{property: PropertyTestSuite, testClass: ?TestSuite}> $properties
     * @param ?positive-int                                                                      $randomSeed
     */
    private function __construct(array $properties, ?int $randomSeed)
    {
        $this->properties = $properties;
        $this->randomSeed = $randomSeed;

        $pid = getmypid();

        register_shutdown_function(
            function () use ($pid): void
            {
                // a process that is forked while an example runs does not
                // finish the test class of the process from which it was forked
                if ($pid !== getmypid()) {
                    // @codeCoverageIgnoreStart
                    return;
                    // @codeCoverageIgnoreEnd
                }

                $this->finishPreparedTestClass();
            },
        );
    }

    /**
     * The properties of the test suite, in the order in which the test runner
     * runs them by default.
     *
     * @return list<array{id: non-empty-string, class: class-string, method: non-empty-string, file: non-empty-string}>
     */
    public function properties(): array
    {
        $properties = [];

        foreach ($this->properties as $id => $property) {
            $test = self::testOf($property['property'])->valueObjectForEvents();

            $properties[] = [
                'id'     => $id,
                'class'  => $test->className(),
                'method' => $test->methodName(),
                'file'   => $test->file(),
            ];
        }

        return $properties;
    }

    /**
     * The random seed with which the random number generators are seeded
     * before each example: the one that was passed while PHPUnit was booted,
     * or the one that PHPUnit chose because the configuration file configures
     * a random execution order. Booting PHPUnit in another process with it
     * seeds them in the same way there. Null when they are not seeded.
     *
     * @return ?positive-int
     */
    public function randomSeed(): ?int
    {
        return $this->randomSeed;
    }

    /**
     * Runs an example of the property with arguments that are generated from
     * the given choices.
     *
     * @param non-empty-string       $id           the ID of the property, as properties() returns it
     * @param list<non-negative-int> $choices
     * @param positive-int           $timeLimit    the time limit of the example in seconds, which covers generating its arguments and running it
     * @param bool                   $exportValues whether the result has the exported arguments, drawn values, and steps of the example
     *
     * @throws Exception                     when the property is invalid, or its generators cannot be created
     * @throws InvalidArgumentException
     * @throws PropertyDoesNotExistException
     *
     * @return Result
     */
    public function replay(string $id, array $choices, int $timeLimit, bool $exportValues = false): array
    {
        if (!array_is_list($choices)) {
            throw new InvalidArgumentException('The choices are not a list');
        }

        foreach ($choices as $choice) {
            if (!is_int($choice) || $choice < 0) {
                throw new InvalidArgumentException('A choice is not a non-negative integer');
            }
        }

        $this->ensureIsPositive($timeLimit);

        $property = $this->property($id);

        return self::asArray(
            $this->resultOfTestClass($property['testClass']) ??
            $property['property']->replayExample($choices, $timeLimit, $exportValues),
        );
    }

    /**
     * Runs an example of the property with arguments that are generated from
     * choices that are drawn from a random number generator that is seeded
     * with the given seed. The same seed generates the same example.
     *
     * @param non-empty-string $id           the ID of the property, as properties() returns it
     * @param positive-int     $timeLimit    the time limit of the example in seconds, which covers generating its arguments and running it
     * @param bool             $exportValues whether the result has the exported arguments, drawn values, and steps of the example
     *
     * @throws Exception                     when the property is invalid, or its generators cannot be created
     * @throws InvalidArgumentException
     * @throws PropertyDoesNotExistException
     *
     * @return Result
     */
    public function generate(string $id, int $seed, int $timeLimit, bool $exportValues = false): array
    {
        $this->ensureIsPositive($timeLimit);

        $property = $this->property($id);

        return self::asArray(
            $this->resultOfTestClass($property['testClass']) ??
            $property['property']->generateExample($seed, $timeLimit, $exportValues),
        );
    }

    /**
     * Calls the hook methods of the test class that are called before its
     * first test unless it is the test class that was prepared last, and
     * returns the result of every example of a property of the test class
     * when they did not succeed, or null when they did.
     *
     * One test class is prepared at a time, as in a test run: before the
     * first example of a property of another test class is run, the hook
     * methods of the test class that was prepared last that are called after
     * its last test are called. When an example of one of its properties is
     * run again later, the test class is prepared again.
     *
     * @throws Exception
     */
    private function resultOfTestClass(?TestSuite $testClass): ?ExampleResult
    {
        if ($testClass === null) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        if ($this->preparedTestClass === null || $this->preparedTestClass['testClass'] !== $testClass) {
            $this->finishPreparedTestClass();

            $this->preparedTestClass = [
                'testClass' => $testClass,
                'outcome'   => $testClass->invokeMethodsBeforeFirstExample(),
            ];
        }

        $outcome = $this->preparedTestClass['outcome'];

        if ($outcome === null) {
            return null;
        }

        if (is_string($outcome)) {
            return ExampleResult::skipped(new ChoiceSequence([]));
        }

        return ExampleResult::aborted(Falsification::ofThrowable($outcome), new ChoiceSequence([]));
    }

    /**
     * Calls the hook methods of the test class that was prepared last that
     * are called after its last test, unless the ones that are called before
     * its first test did not succeed, as in a test run.
     */
    private function finishPreparedTestClass(): void
    {
        $preparedTestClass = $this->preparedTestClass;

        $this->preparedTestClass = null;

        if ($preparedTestClass !== null && $preparedTestClass['outcome'] === null) {
            $preparedTestClass['testClass']->invokeMethodsAfterLastExample();
        }
    }

    /**
     * @param non-empty-string $id
     *
     * @throws PropertyDoesNotExistException
     *
     * @return array{property: PropertyTestSuite, testClass: ?TestSuite}
     */
    private function property(string $id): array
    {
        if (!isset($this->properties[$id])) {
            throw new PropertyDoesNotExistException($id);
        }

        return $this->properties[$id];
    }

    /**
     * @phpstan-assert positive-int $timeLimit
     *
     * @throws InvalidArgumentException
     */
    private function ensureIsPositive(int $timeLimit): void
    {
        if ($timeLimit < 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'The time limit must be at least one second, %d seconds given',
                    $timeLimit,
                ),
            );
        }
    }

    /**
     * @param ?non-empty-string $configurationFile
     * @param ?non-empty-string $filter
     * @param ?positive-int     $randomSeed
     *
     * @throws ConfigurationFileNotFoundException
     * @throws Exception
     */
    private static function testSuite(?string $configurationFile, ?string $filter, ?int $randomSeed): TestSuite
    {
        $emitter   = EventFacade::emitter();
        $arguments = ['--no-output', '--no-coverage', '--no-logging', '--no-extensions'];

        if ($configurationFile !== null) {
            $arguments[] = '--configuration';
            $arguments[] = $configurationFile;
        }

        if ($filter !== null) {
            $arguments[] = '--filter';
            $arguments[] = $filter;
        }

        if ($randomSeed !== null) {
            $arguments[] = '--random-seed';
            $arguments[] = (string) $randomSeed;
        }

        $cliConfiguration           = new Builder($emitter)->fromParameters($arguments);
        $pathToXmlConfigurationFile = new XmlConfigurationFileFinder()->find($cliConfiguration);

        // the configuration file that is passed is a directory that does not
        // contain one
        if ($pathToXmlConfigurationFile === false && $configurationFile !== null) {
            throw new ConfigurationFileNotFoundException($configurationFile);
        }

        if ($pathToXmlConfigurationFile === false) {
            $xmlConfiguration = DefaultConfiguration::create();
        } else {
            $xmlConfiguration = new Loader()->load($pathToXmlConfigurationFile);
        }

        // PHPUnit's global state is changed from here on: a boot that failed
        // before, because the configuration file cannot be found or read,
        // changed nothing and can be retried
        self::$booted = true;

        $configuration = Registry::init($cliConfiguration, $xmlConfiguration, $emitter);

        DifferBuilder::configureComparatorFactory();

        new PhpHandler($emitter)->handle($configuration->php());
        new BootstrapLoader($emitter)->handle($configuration);

        // the expectations of a test for deprecations are verified against
        // the deprecations that are collected while it runs
        DeprecationCollector::init();

        // no other subscribers are registered: the events are neither printed
        // nor logged, and the ones that are emitted while an example runs are
        // collected for the example and then discarded
        EventFacade::instance()->seal();

        new ErrorHandlerConfigurator($emitter)->configure($configuration);

        ErrorHandler::instance()->registerForNonTestCaseContext();

        try {
            $testSuite = new TestSuiteBuilder($emitter, new NullTestFileSkipper)->build($configuration);
        } finally {
            ErrorHandler::instance()->restoreForNonTestCaseContext();
        }

        new TestSuiteFilterProcessor($emitter)->process($configuration, $testSuite);

        return $testSuite;
    }

    /**
     * @return array<non-empty-string, array{property: PropertyTestSuite, testClass: ?TestSuite}>
     */
    private static function propertiesIn(TestSuite $testSuite, ?TestSuite $testClass): array
    {
        $properties = [];

        foreach ($testSuite as $test) {
            if ($test instanceof PropertyTestSuite) {
                // a filter leaves the suite of a property in the test suite,
                // and leaves out the test of the property when it does not
                // match
                foreach ($test as $property) {
                    if ($property instanceof TestCase) {
                        $properties[$property->valueObjectForEvents()->id()] = ['property' => $test, 'testClass' => $testClass];
                    }
                }

                continue;
            }

            if ($test instanceof TestSuite) {
                $properties = [...$properties, ...self::propertiesIn($test, $test->isForTestClass() ? $test : $testClass)];
            }
        }

        return $properties;
    }

    private static function testOf(PropertyTestSuite $property): TestCase
    {
        $test = $property->tests()[0] ?? null;

        if (!$test instanceof TestCase) {
            // @codeCoverageIgnoreStart
            throw new PropertyDoesNotExistException($property->name());
            // @codeCoverageIgnoreEnd
        }

        return $test;
    }

    /**
     * @return Result
     */
    private static function asArray(ExampleResult $result): array
    {
        $sequence = $result->sequence();
        $choices  = $sequence->choices();
        $spans    = [];
        $maxima   = [];

        foreach ($sequence->spans() as $span) {
            $spans[] = [
                'start'  => $span->start(),
                'end'    => $span->end(),
                'label'  => $span->label(),
                'parent' => $span->parent(),
                'kind'   => match ($span->kind()) {
                    SpanKind::Bytes      => 'bytes',
                    SpanKind::Characters => 'characters',
                    SpanKind::Element    => 'element',
                    null                 => null,
                },
            ];
        }

        for ($i = 0; $i < count($choices); $i++) {
            $maxima[] = $sequence->maximumAt($i);
        }

        $failure       = null;
        $falsification = $result->falsification();

        if ($falsification !== null) {
            $signature = $falsification->signature();

            $failure = [
                'kind'        => $signature->kind(),
                'signature'   => $signature->text(),
                'class'       => $falsification->className(),
                'location'    => $falsification->location(),
                'description' => $falsification->description(),
                'message'     => $falsification->message(),
            ];
        }

        return [
            'outcome'     => $result->outcome(),
            'failure'     => $failure,
            'choices'     => $choices,
            'spans'       => $spans,
            'maxima'      => $maxima,
            'arguments'   => $result->arguments(),
            'drawnValues' => $result->drawnValues(),
            'steps'       => $result->steps(),
        ];
    }
}
