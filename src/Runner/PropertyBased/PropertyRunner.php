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
use function array_key_exists;
use function array_reverse;
use function count;
use function is_int;
use function is_string;
use function register_shutdown_function;
use function sprintf;
use AssertionError;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\PropertyBased\ExampleResult;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
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
 * PHPUnit is booted once per process. The configuration file and its
 * bootstrap script are loaded as for a test run, but nothing is printed, no
 * code coverage is collected, nothing is logged, and no extensions are
 * loaded.
 *
 * An example is run with its full lifecycle, from the hook methods that are
 * called before the property to the ones that are called after it, and with
 * the time limit that is passed. Its arguments are generated from choices as
 * when PHPUnit checks the property, and it draws values with draw() from the
 * same choices. It is neither shrunk nor reported. The issues that it
 * triggers are handled as in a test run, but they are not reported either.
 *
 * The hook methods of a test class that are called before its first test are
 * called before the first example of one of its properties is run, and the
 * ones that are called after its last test are called when the process ends.
 * When one of the former raises an exception, every example of a property of
 * the test class falsifies the property with that exception, and when one of
 * them skips the tests of the test class, every example is skipped.
 *
 * The result of an example tells how it ended, how it falsified the property,
 * and which choices it consumed, with their spans and the maxima of the
 * choices. On request, it also has the arguments of the example, the values
 * that it drew with draw(), and the steps of its state machine, exported as
 * PHPUnit reports them for a counterexample. They are null otherwise, and
 * when no arguments were generated for the example.
 *
 * @phpstan-type Result array{outcome: 'errored'|'failed'|'incomplete'|'passed'|'rejected'|'skipped'|'timeout', failure: ?array{kind: 'description'|'throwable'|'timeout', signature: non-empty-string, class: ?class-string, location: ?non-empty-string, description: non-empty-string, message: string}, choices: list<non-negative-int>, spans: list<array{start: non-negative-int, end: non-negative-int, label: string, parent: ?non-negative-int, kind: null|'bytes'|'element'}>, maxima: list<?non-negative-int>, arguments: ?array<non-empty-string, string>, drawnValues: ?list<array{label: ?non-empty-string, value: string}>, steps: ?list<non-empty-string>}
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
     * What calling the hook methods that are called before the first test of
     * a test class resulted in, by the name of the test class: null when they
     * were called, the message why its tests are skipped, or the throwable
     * that one of them raised.
     *
     * @var array<string, null|non-empty-string|Throwable>
     */
    private array $preparedTestClasses = [];

    /**
     * The test classes whose hook methods that are called after their last
     * test are called when the process ends, in the order in which they were
     * prepared.
     *
     * @var list<TestSuite>
     */
    private array $testClassesToBeFinished = [];

    /**
     * Loads the configuration file and its bootstrap script and builds the
     * test suite, with only the tests that match the filter, as with the
     * --filter option of the test runner.
     *
     * @param ?non-empty-string $configurationFile the configuration file, null to look for one in the working directory as the test runner does
     * @param ?non-empty-string $filter            the filter, null for all tests
     *
     * @throws AlreadyBootedException
     * @throws Exception                          when the configuration file, its bootstrap script, or the test suite cannot be loaded
     * @throws TimeLimitCannotBeEnforcedException
     */
    public static function boot(?string $configurationFile = null, ?string $filter = null): self
    {
        if (self::$booted) {
            throw new AlreadyBootedException;
        }

        if (!new Invoker()->canInvokeWithTimeout()) {
            throw new TimeLimitCannotBeEnforcedException;
        }

        self::$booted = true;

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

        $cliConfiguration           = new Builder($emitter)->fromParameters($arguments);
        $pathToXmlConfigurationFile = new XmlConfigurationFileFinder()->find($cliConfiguration);

        if ($pathToXmlConfigurationFile === false) {
            $xmlConfiguration = DefaultConfiguration::create();
        } else {
            $xmlConfiguration = new Loader()->load($pathToXmlConfigurationFile);
        }

        $configuration = Registry::init($cliConfiguration, $xmlConfiguration, $emitter);

        DifferBuilder::configureComparatorFactory();

        new PhpHandler($emitter)->handle($configuration->php());
        new BootstrapLoader($emitter)->handle($configuration);

        // no subscribers are registered: the events are neither printed nor
        // logged, and the ones that are emitted while an example runs are
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

        return new self(self::propertiesIn($testSuite, null));
    }

    /**
     * @param array<non-empty-string, array{property: PropertyTestSuite, testClass: ?TestSuite}> $properties
     */
    private function __construct(array $properties)
    {
        $this->properties = $properties;
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
     * Runs an example of the property with arguments that are generated from
     * the given choices.
     *
     * @param non-empty-string       $id           the ID of the property, as properties() returns it
     * @param list<non-negative-int> $choices
     * @param positive-int           $timeLimit    the time limit of the example in seconds
     * @param bool                   $exportValues whether the result has the exported arguments, drawn values, and steps of the example
     *
     * @throws Exception                     when the property is invalid
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
     * @param positive-int     $timeLimit    the time limit of the example in seconds
     * @param bool             $exportValues whether the result has the exported arguments, drawn values, and steps of the example
     *
     * @throws Exception                     when the property is invalid
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
     * first test unless they were called before, and returns the result of
     * every example of a property of the test class when they did not succeed,
     * or null when they did.
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

        $name = $testClass->name();

        if (array_key_exists($name, $this->preparedTestClasses)) {
            $outcome = $this->preparedTestClasses[$name];
        } else {
            $outcome = $testClass->invokeMethodsBeforeFirstExample();

            $this->preparedTestClasses[$name] = $outcome;

            if ($outcome === null) {
                $this->finishWhenTheProcessEnds($testClass);
            }
        }

        if ($outcome === null) {
            return null;
        }

        if (is_string($outcome)) {
            return ExampleResult::skipped(new ChoiceSequence([]));
        }

        return ExampleResult::falsified(
            Falsification::ofThrowable($outcome),
            $outcome instanceof AssertionFailedError || $outcome instanceof AssertionError,
            new ChoiceSequence([]),
        );
    }

    private function finishWhenTheProcessEnds(TestSuite $testClass): void
    {
        if ($this->testClassesToBeFinished === []) {
            register_shutdown_function(
                function (): void
                {
                    foreach (array_reverse($this->testClassesToBeFinished) as $testClass) {
                        $testClass->invokeMethodsAfterLastExample();
                    }
                },
            );
        }

        $this->testClassesToBeFinished[] = $testClass;
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
                    SpanKind::Bytes   => 'bytes',
                    SpanKind::Element => 'element',
                    null              => null,
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
