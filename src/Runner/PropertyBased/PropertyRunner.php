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

use const SIGALRM;
use const STDERR;
use function array_is_list;
use function array_reverse;
use function assert;
use function class_exists;
use function count;
use function defined;
use function function_exists;
use function fwrite;
use function getmypid;
use function hrtime;
use function intdiv;
use function is_callable;
use function is_int;
use function is_string;
use function max;
use function ob_end_clean;
use function ob_get_level;
use function pcntl_alarm;
use function pcntl_async_signals;
use function pcntl_signal;
use function pcntl_signal_get_handler;
use function register_shutdown_function;
use function restore_error_handler;
use function set_error_handler;
use function spl_object_id;
use function sprintf;
use Closure;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\TestRunner\PhpunitWarningTriggered;
use PHPUnit\Exception;
use PHPUnit\Framework\PropertyBased\ExampleResult;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\ShutdownHandler;
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
 * PHPUnit is booted once per process, and it cannot be booted in a process
 * in which it is already running, by a test that the test runner runs, for
 * instance. A boot that fails because the configuration file cannot be found
 * or read changes nothing and can be retried. A boot that fails for another
 * reason cannot be retried, see boot(). The configuration file and its
 * bootstrap script are loaded as for a test run, but nothing is printed, no
 * code coverage is collected, nothing is logged, and no extensions are
 * loaded. As nothing is printed, an error that PHPUnit triggers while it is
 * booted, about a property that cannot be checked, for instance, is raised
 * as an exception, as the test run fails because of it. A warning that
 * PHPUnit triggers while it is booted, about a configuration file that did
 * not pass validation, for instance, is raised as an exception when the test
 * run fails because of it, which it does unless the configuration file sets
 * failOnPhpunitWarning to false, and is discarded otherwise. This is so for
 * every test in the test suite that the configuration file configures, also
 * for one that the filter leaves out, as the test suite is built before it
 * is filtered.
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
 * While code of the project is run, when PHPUnit is booted, while an example
 * runs, and while the hook methods of a test class that are called before
 * its first test and after its last test are called, the error handlers of
 * the caller are hidden: they are not called, and PHPUnit's error handler
 * does not forward an issue to them. The error handlers that the bootstrap
 * script of the project and these hook methods register are the ones of the
 * project: as in a test run, they are registered while code of the project
 * is run, and only then. Afterwards, the error handlers of the caller are
 * registered as before, for the error levels that they were registered for,
 * also when code of the project did not remove an error handler that it
 * registered, in a generator, for instance. Only code of the project that
 * removes at least two error handlers more than it registered removes error
 * handlers of the caller: the one that was active is then registered again,
 * for all error levels.
 *
 * The output that an example prints is not printed, as in a test run. Output
 * buffering that code of the project started without ending it, in a
 * generator, for instance, is ended afterwards, and what was buffered is
 * discarded, so that the level of output buffering of the caller is the one
 * from before.
 *
 * The time limit of an example is enforced with the SIGALRM signal. While an
 * example runs, the handler of this signal, a pending alarm, and whether
 * signals are handled asynchronously are PHPUnit's. Afterwards, the ones of
 * the caller are restored, and an alarm of the caller is set again for the
 * rest of its time: an alarm that would have gone off while the example ran
 * goes off one second after it. As in a test run, an example that catches the
 * exception with which it is aborted when it exceeds its time limit, as code
 * that catches every throwable does, keeps running.
 *
 * When an example ends the process, with exit() or with a fatal error, for
 * instance, its output is discarded, the handler of the SIGALRM signal, the
 * alarm, and whether signals are handled asynchronously of the caller are
 * restored, and the error handlers of the caller are registered as before,
 * in a shutdown function that is registered when PHPUnit is booted, before
 * the hook methods of the prepared test class that are called after its last
 * test are called. PHPUnit prints nothing, unless one of these hook methods
 * raises an exception, which is written to the standard error stream, see
 * below, and the process ends with the exit status of the example. The
 * shutdown functions that the caller registers after booting PHPUnit are
 * called after it, and the ones that it registers before are called before
 * it.
 *
 * As in a test run, one test class is prepared at a time. The hook methods of
 * a test class that are called before its first test are called before the
 * first example of one of its properties is run, and the ones that are called
 * after its last test are called before an example of a property of another
 * test class is run, when finish() is called, or when the process ends.
 * When one of the former raises an exception, every example of a property of
 * the test class is aborted with that exception, and when one of them skips
 * the tests of the test class, every example is skipped. The latter are only
 * called in the process that prepared the test class: a process that is
 * forked from it, while an example runs or between examples, does not call
 * them for this test class, but does for a test class that it prepares
 * itself.
 *
 * When one of the hook methods that are called after the last test of a test
 * class raises an exception, or marks the test as incomplete or skipped, no
 * example is left to which this could be attributed: an
 * AfterLastTestMethodFailedException is raised instead, by finish(), or by
 * replay() or generate() for a property of another test class, before the
 * test class of this property is prepared and the example is run. The test
 * class that was prepared is finished then, and its hook methods are not
 * called again, so that the call can be repeated, which prepares the other
 * test class and runs the example. When the process ends, nothing can be
 * raised: the message of the exception, followed by a line break, is written
 * to the standard error stream instead, and the exit status of the process is
 * not changed. Nothing is written for a test class that was finished before,
 * with finish(), for instance.
 *
 * The result of an example tells how it ended, how it falsified the
 * property, and which choices it consumed, with their spans and the maxima
 * of the choices. The kind of a span, when it has one, is the name of the
 * case of SpanKind of sebastian/choice-sequence in lowercase: 'bytes',
 * 'characters', 'element', 'requiredelement', 'referable', 'reference', or
 * 'consumingreference'.
 * On request, the result also has the arguments
 * of the example, the values that it drew with draw(), and the steps of its
 * state machine, exported as PHPUnit reports them for a counterexample. They
 * are null otherwise, and when no arguments were generated for the example.
 *
 * The signature of how an example falsified the property is the same for
 * the same failure in every process, whatever its working directory, so that
 * failures that are found in different processes can be compared by it. The
 * path of a file below the directory of the configuration file is relative
 * to it, and the signature does not name the class of an anonymous throwable
 * or of a test double, whose names differ between processes, but the first
 * class that it extends which is neither. The class of the throwable itself
 * is kept as it is.
 *
 * An example that triggers an issue for which the test run fails, a warning
 * when the test runner is configured to fail on warnings, for instance,
 * falsifies the property like an example that fails. Its failure has no
 * class, and its signature names the kind of the issue and where it was
 * triggered.
 *
 * An example that fails or errors without checking the property is aborted
 * rather than falsifying it: when a generator raises an exception while its
 * arguments are generated, when it ends before its test method is called, in
 * setUp(), for instance, or when its state machine cannot be run. When PHPUnit
 * checks the property, such an example is the error of the property, which is
 * reported without being shrunk. An example whose arguments take longer to
 * generate than its time limit allows is aborted, too. The failure of an
 * aborted example tells how it ended.
 *
 * @phpstan-type Result array{outcome: 'aborted'|'errored'|'failed'|'incomplete'|'passed'|'rejected'|'skipped'|'timeout', failure: ?array{kind: 'description'|'throwable'|'timeout', signature: non-empty-string, class: ?class-string, location: ?non-empty-string, description: non-empty-string, message: string}, choices: list<non-negative-int>, spans: list<array{start: non-negative-int, end: non-negative-int, label: string, parent: ?non-negative-int, kind: null|'bytes'|'characters'|'consumingreference'|'element'|'referable'|'reference'|'requiredelement'}>, maxima: list<?non-negative-int>, arguments: ?array<non-empty-string, string>, drawnValues: ?list<array{label: ?non-empty-string, value: string}>, steps: ?list<non-empty-string>}
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class PropertyRunner
{
    /**
     * Whether booting PHPUnit changed its global state, after which it cannot
     * be booted again in this process, also when booting it failed.
     */
    private static bool $booted = false;

    /**
     * Whether PHPUnit was booted without booting it having failed.
     */
    private static bool $bootedSuccessfully = false;

    /**
     * @var array<non-empty-string, array{property: PropertyTestSuite, test: TestMethod, testClass: TestSuite}>
     */
    private readonly array $properties;

    /**
     * The suites of the properties of each test class, by the ID of the
     * object of the suite of the test class, so that finishing a test class
     * does not take time that grows with the number of properties of the
     * test suite.
     *
     * @var array<int, non-empty-list<PropertyTestSuite>>
     */
    private readonly array $propertiesOfTestClass;

    /**
     * @var ?positive-int
     */
    private readonly ?int $randomSeed;

    /**
     * The error handlers of the project, from the one that was registered
     * first to the one that was registered last: the ones that were
     * registered while PHPUnit was booted, by the bootstrap script, for
     * instance, and the ones that the hook methods of the test classes that
     * are called before their first test and after their last test
     * registered, without the ones that they removed.
     *
     * @var list<callable>
     */
    private array $errorHandlersOfProject;

    /**
     * While code of the project is run, what is restored for the caller
     * afterwards, or when the code of the project ends the process: the error
     * handler that was active, the marker below which the error handlers of
     * the caller are hidden, and the level of output buffering before.
     *
     * @var ?array{errorHandler: null|array<mixed>|callable|string, marker: Closure(): bool, outputBufferingLevel: int}
     */
    private ?array $stateOfCallerWhileCodeOfProjectRuns = null;

    /**
     * While an example runs, what is restored for the caller after it, or
     * when it ends the process: the handler of the SIGALRM signal, whether
     * signals are handled asynchronously, the seconds that were left until an
     * alarm of the caller, when the example started, and the process in which
     * it started.
     *
     * @var ?array{handler: callable|int, asynchronous: bool, remaining: int, started: int, process: false|int}
     */
    private ?array $stateOfCaller = null;

    /**
     * The test class whose hook methods that are called before its first test
     * were called last, and what calling them resulted in: null when they
     * were called, the message why its tests are skipped, or the throwable
     * that one of them raised, and the process in which they were called.
     *
     * @var ?array{testClass: TestSuite, outcome: null|non-empty-string|Throwable, process: false|int}
     */
    private ?array $preparedTestClass = null;

    /**
     * Loads the configuration file and its bootstrap script and builds the
     * test suite, with only the tests that match the filter, as with the
     * --filter option of the test runner.
     *
     * Booting PHPUnit can only be retried when it failed because the
     * configuration file cannot be found or read. When it failed for another
     * reason, because the bootstrap script or the test suite cannot be loaded,
     * or because PHPUnit triggered an error or a warning, for instance, the
     * global state of PHPUnit and of PHP has been changed in ways that cannot
     * be reset: the constants and settings of the configuration file are
     * defined, the bootstrap script and the test classes are loaded, and
     * PHPUnit is configured for this configuration file. Every further call
     * then raises an AlreadyBootedException, also one with another
     * configuration file or another filter, so that examples can only be run
     * in another process.
     *
     * @param ?non-empty-string $configurationFile the configuration file, null to look for one in the working directory as the test runner does
     * @param ?non-empty-string $filter            the filter, null for all tests
     * @param ?positive-int     $randomSeed        the random seed with which the random number generators are seeded before each example, as with the --random-seed option of the test runner, null to seed them only when the configuration file configures a random execution order
     *
     * @throws AlreadyBootedException                when PHPUnit was booted before, also when booting it failed after its global state was changed, or is already running in this process
     * @throws ConfigurationFileNotFoundException    when the configuration file that is passed is a directory without one
     * @throws ErrorTriggeredWhileBootingException   when PHPUnit triggered an error while the test suite was built, for a property that cannot be checked, for instance
     * @throws Exception                             when the configuration file, its bootstrap script, or the test suite cannot be loaded
     * @throws InvalidArgumentException
     * @throws TimeLimitCannotBeEnforcedException
     * @throws WarningTriggeredWhileBootingException when PHPUnit triggered a warning while the configuration file was loaded or the test suite was built, and the test run fails because of it
     */
    public static function boot(?string $configurationFile = null, ?string $filter = null, ?int $randomSeed = null): self
    {
        if (self::$booted) {
            if (self::$bootedSuccessfully) {
                throw new AlreadyBootedException;
            }

            throw new AlreadyBootedException('PHPUnit cannot be booted again in this process because booting it failed before, after it had changed global state that cannot be reset');
        }

        // the configuration is initialized before the test runner runs any
        // code that is not PHPUnit's own, the bootstrap script, an extension,
        // or a test, for instance: booting PHPUnit again would replace the
        // global state of the test run
        if (Registry::isInitialized()) {
            throw new AlreadyBootedException('PHPUnit cannot be booted because it is already running in this process');
        }

        if ($configurationFile === '') {
            throw new InvalidArgumentException('The configuration file must not be an empty string');
        }

        if ($filter === '') {
            throw new InvalidArgumentException('The filter must not be an empty string');
        }

        if ($randomSeed !== null && $randomSeed < 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'The random seed must be a positive integer, %d given',
                    $randomSeed,
                ),
            );
        }

        // the handler of the SIGALRM signal of the caller, which is restored
        // after an example, is asked for with a function that php-invoker
        // does not need to enforce a time limit
        if (!new Invoker()->canInvokeWithTimeout() || !function_exists('pcntl_signal_get_handler')) {
            throw new TimeLimitCannotBeEnforcedException;
        }

        $facade = EventFacade::instance();

        // the events that are emitted while PHPUnit is booted are collected
        // rather than dispatched, so that the errors and warnings among them,
        // about a property that cannot be checked or a configuration file that
        // did not pass validation, for instance, are raised instead of being
        // lost
        $facade->startCollectingEvents();

        $errorHandlersOfCaller = self::hideErrorHandlersOfCaller([]);

        try {
            $testSuite = self::testSuite($configurationFile, $filter, $randomSeed);
        } finally {
            $events = $facade->stopCollectingEvents();

            // the error handlers that are registered now are the ones of the
            // project, which its bootstrap script registered, for instance
            $errorHandlersOfProject = self::removeErrorHandlersOfProject($errorHandlersOfCaller['marker']);

            self::revealErrorHandlersOfCaller($errorHandlersOfCaller);
        }

        $errors   = [];
        $warnings = [];

        foreach ($events as $event) {
            if ($event instanceof PhpunitErrorTriggered) {
                $errors[] = [
                    'test'    => $event->test()->id(),
                    'message' => $event->message(),
                ];
            }

            if ($event instanceof PhpunitWarningTriggered) {
                $warnings[] = $event->message();
            }
        }

        // the errors are raised first, as a warning can follow from one, that
        // no tests were found in a test class whose properties cannot be
        // checked, for instance
        if ($errors !== []) {
            throw new ErrorTriggeredWhileBootingException($errors);
        }

        $configuration = Registry::get();

        // as PHPUnit\TextUI\ShellExitCodeCalculator decides whether the test
        // run fails because of a warning that PHPUnit triggered
        $failOnPhpunitWarning = ($configuration->failOnAllIssues() || $configuration->failOnPhpunitWarning()) &&
                                !$configuration->doNotFailOnPhpunitWarning();

        if ($warnings !== [] && $failOnPhpunitWarning) {
            throw new WarningTriggeredWhileBootingException($warnings);
        }

        // the random seed is null when the random number generators are not
        // seeded, as the one that PHPUnit chose then has no effect on the
        // examples
        $randomSeed = null;

        if ($configuration->seedRandomNumberGenerators()) {
            $randomSeed = $configuration->randomSeed();
        }

        $runner = new self(self::propertiesIn($testSuite), $randomSeed, $errorHandlersOfProject);

        self::$bootedSuccessfully = true;

        return $runner;
    }

    /**
     * @param array<non-empty-string, array{property: PropertyTestSuite, test: TestMethod, testClass: TestSuite}> $properties
     * @param ?positive-int                                                                                       $randomSeed
     * @param list<callable>                                                                                      $errorHandlersOfProject
     */
    private function __construct(array $properties, ?int $randomSeed, array $errorHandlersOfProject)
    {
        $this->properties             = $properties;
        $this->errorHandlersOfProject = $errorHandlersOfProject;

        $propertiesOfTestClass = [];

        foreach ($properties as $property) {
            $propertiesOfTestClass[spl_object_id($property['testClass'])][] = $property['property'];
        }

        $this->propertiesOfTestClass = $propertiesOfTestClass;
        $this->randomSeed            = $randomSeed;

        register_shutdown_function(
            function (): void
            {
                // an example that ended the process, with exit() or with a
                // fatal error, for instance, did not restore what was changed
                // for it: its output is still buffered, PHPUnit's error
                // handler is still enabled, the error handlers of the caller
                // are still hidden, and its time limit is still enforced
                ErrorHandler::instance()->disable();

                $this->restoreCallerAfterCodeOfProject();

                $this->restoreSignalStateOfCaller();

                // nothing is printed, and the process ends with the exit
                // status of the example, rather than with the message and the
                // exit status of a test run that ended prematurely
                ShutdownHandler::resetMessage();

                $this->prepareForCodeOfProject();

                $exception = null;

                try {
                    $this->finishPreparedTestClass();
                } catch (AfterLastTestMethodFailedException $exception) {
                } finally {
                    $this->restoreCallerAfterCodeOfProject();
                }

                // nothing can be raised while the process ends: what a hook
                // method that is called after the last test of the test class
                // raised is written to the standard error stream, which only
                // the CLI has, and the exit status is left as it is
                if ($exception !== null && defined('STDERR')) {
                    fwrite(STDERR, $exception->getMessage() . "\n");
                }
            },
        );
    }

    /**
     * PHPUnit is booted once per process, and its state is the one of the
     * process: a copy would prepare a test class that is already prepared
     * again, and would not finish it when the process ends.
     *
     * @codeCoverageIgnore
     */
    private function __clone()
    {
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
            $test = $property['test'];

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
     * The given choices need not be the ones that an example consumes: a
     * choice that is greater than the maximum of the choice that is consumed
     * in its place is replaced with this maximum, a choice that is consumed
     * after the last of the given choices is 0, and the given choices that
     * are left when the example has consumed its choices are ignored. The
     * choices of the result are the ones that the example consumed.
     *
     * @param non-empty-string       $id           the ID of the property, as properties() returns it
     * @param list<non-negative-int> $choices
     * @param positive-int           $timeLimit    the time limit of the example in seconds, which covers generating its arguments and running it
     * @param bool                   $exportValues whether the result has the exported arguments, drawn values, and steps of the example
     *
     * @throws AfterLastTestMethodFailedException when a hook method that is called after the last test of the test class that was prepared last, which is not the one of the property, raised an exception: the example was not run, and is run when the call is repeated
     * @throws Exception                          when the property is invalid, or its generators cannot be created
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
            $this->run(
                $property['testClass'],
                static fn (): ExampleResult => $property['property']->replayExample($choices, $timeLimit, $exportValues),
            ),
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
     * @throws AfterLastTestMethodFailedException when a hook method that is called after the last test of the test class that was prepared last, which is not the one of the property, raised an exception: the example was not run, and is run when the call is repeated
     * @throws Exception                          when the property is invalid, or its generators cannot be created
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
            $this->run(
                $property['testClass'],
                static fn (): ExampleResult => $property['property']->generateExample($seed, $timeLimit, $exportValues),
            ),
        );
    }

    /**
     * Calls the hook methods of the test class that was prepared last that
     * are called after its last test, as they are called before an example of
     * a property of another test class is run, rather than only when the
     * process ends. When an example of one of its properties is run
     * afterwards, the test class is prepared again.
     *
     * Nothing is done when no test class is prepared: before the first
     * example, and when the test class that was prepared last is already
     * finished. As before an example of a property of another test class is
     * run, the hook methods are not called when the ones that are called
     * before the first test of the test class did not succeed, and not in a
     * process that is forked from the one that prepared the test class.
     *
     * @throws AfterLastTestMethodFailedException when one of the hook methods raised an exception: the test class is finished, and its hook methods are not called again
     */
    public function finish(): void
    {
        if ($this->preparedTestClass === null) {
            return;
        }

        $this->prepareForCodeOfProject();

        try {
            $this->finishPreparedTestClass();
        } finally {
            // the test class of the next example is prepared by another
            // call, for which the error handlers of the project are the ones
            // that the hook methods left registered
            $this->rememberErrorHandlersOfProject();

            $this->restoreCallerAfterCodeOfProject();
        }
    }

    /**
     * Prepares the test class of a property unless it is the test class that
     * was prepared last, and runs an example of the property, with the error
     * handlers of the project instead of the ones of the caller.
     *
     * @param Closure(): ExampleResult $example
     *
     * @throws AfterLastTestMethodFailedException
     * @throws Exception
     */
    private function run(TestSuite $testClass, Closure $example): ExampleResult
    {
        $this->prepareForCodeOfProject();

        try {
            $result = $this->resultOfTestClass($testClass);

            if ($result === null) {
                $result = $this->suspendingAlarmOfCaller($example);
            }

            return $result;
        } finally {
            $this->restoreCallerAfterCodeOfProject();
        }
    }

    /**
     * Runs an example, whose time limit php-invoker enforces with the SIGALRM
     * signal: it replaces the handler of this signal, cancels a pending alarm,
     * and lets signals be handled asynchronously, without restoring any of
     * this. As these belong to the caller, they are restored after the
     * example, and an alarm of the caller is set again for the rest of its
     * time, for at least one second.
     *
     * @param Closure(): ExampleResult $example
     */
    private function suspendingAlarmOfCaller(Closure $example): ExampleResult
    {
        $this->stateOfCaller = [
            'handler'      => pcntl_signal_get_handler(SIGALRM),
            'asynchronous' => pcntl_async_signals(),
            'remaining'    => pcntl_alarm(0),
            'started'      => hrtime(true),
            'process'      => getmypid(),
        ];

        try {
            return $example();
        } finally {
            $this->restoreSignalStateOfCaller();
        }
    }

    /**
     * Restores the handler of the SIGALRM signal, the alarm, and whether
     * signals are handled asynchronously of the caller after an example, or
     * when the example ended the process.
     *
     * They are only restored in the process in which the example started: a
     * process that the example forked has no alarm of the caller, as alarms
     * are not inherited by forked processes, and setting it again there would
     * call the handler of the caller in that process.
     */
    private function restoreSignalStateOfCaller(): void
    {
        $state = $this->stateOfCaller;

        if ($state === null) {
            return;
        }

        $this->stateOfCaller = null;

        if ($state['process'] !== getmypid()) {
            return;
        }

        pcntl_alarm(0);
        pcntl_signal(SIGALRM, $state['handler']);
        pcntl_async_signals($state['asynchronous']);

        if ($state['remaining'] > 0) {
            pcntl_alarm(max(1, $state['remaining'] - intdiv(hrtime(true) - $state['started'], 1_000_000_000)));
        }
    }

    /**
     * Hides the error handlers of the caller before code of the project is
     * run, and registers the error handlers of the project.
     */
    private function prepareForCodeOfProject(): void
    {
        $errorHandlersOfCaller = self::hideErrorHandlersOfCaller($this->errorHandlersOfProject);

        $this->stateOfCallerWhileCodeOfProjectRuns = [
            'errorHandler'         => $errorHandlersOfCaller['errorHandler'],
            'marker'               => $errorHandlersOfCaller['marker'],
            'outputBufferingLevel' => ob_get_level(),
        ];
    }

    /**
     * Restores what code of the project left changed for the caller, after
     * it was run, or when it ended the process.
     *
     * The error handlers that are registered since the error handlers of the
     * caller were hidden are removed, so that the ones of the caller are
     * registered as before.
     *
     * The output buffering that was started since without being ended is
     * ended, and what was buffered is discarded rather than printed: the
     * output of an example that ended the process, and the output that is
     * buffered because a generator started output buffering, for instance.
     * The hook methods of a test class that are called before its first test
     * and after its last test print, as in a test run.
     */
    private function restoreCallerAfterCodeOfProject(): void
    {
        $state = $this->stateOfCallerWhileCodeOfProjectRuns;

        if ($state === null) {
            return;
        }

        $this->stateOfCallerWhileCodeOfProjectRuns = null;

        self::removeErrorHandlersOfProject($state['marker']);

        while (ob_get_level() > $state['outputBufferingLevel']) {
            if (!ob_end_clean()) {
                break;
            }
        }

        self::revealErrorHandlersOfCaller($state);
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
     * When one of these hook methods raises an exception, the test class is
     * not prepared: the test class that was prepared last is finished then,
     * so that it is prepared when the example is run again.
     *
     * @throws AfterLastTestMethodFailedException
     * @throws Exception
     */
    private function resultOfTestClass(TestSuite $testClass): ?ExampleResult
    {
        if ($this->preparedTestClass === null || $this->preparedTestClass['testClass'] !== $testClass) {
            try {
                $this->finishPreparedTestClass();
            } catch (AfterLastTestMethodFailedException $e) {
                // the test class is prepared by another call, for which the
                // error handlers of the project are the ones that the hook
                // methods left registered
                $this->rememberErrorHandlersOfProject();

                throw $e;
            }

            $outcome = $testClass->invokeMethodsBeforeFirstExample();

            $this->rememberErrorHandlersOfProject();

            $this->preparedTestClass = [
                'testClass' => $testClass,
                'outcome'   => $outcome,
                'process'   => getmypid(),
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
     *
     * The generators of its properties are discarded, as they can depend on
     * what the hook methods that are called before its first test set up,
     * which are called again when the test class is prepared again.
     *
     * @throws AfterLastTestMethodFailedException when one of the hook methods raised an exception
     */
    private function finishPreparedTestClass(): void
    {
        $preparedTestClass = $this->preparedTestClass;

        $this->preparedTestClass = null;

        if ($preparedTestClass === null) {
            return;
        }

        $testClass = spl_object_id($preparedTestClass['testClass']);

        if (isset($this->propertiesOfTestClass[$testClass])) {
            foreach ($this->propertiesOfTestClass[$testClass] as $property) {
                $property->discardGeneratorsOfExamplesRunOnTheirOwn();
            }
        }

        // a process that is forked from the one that prepared the test class,
        // while an example runs or between examples, does not finish it, as
        // the process from which it was forked does
        if ($preparedTestClass['process'] !== getmypid()) {
            return;
        }

        if ($preparedTestClass['outcome'] !== null) {
            return;
        }

        $raised = $preparedTestClass['testClass']->invokeMethodsAfterLastExample();

        if ($raised === null) {
            return;
        }

        $className = $preparedTestClass['testClass']->name();

        assert(class_exists($className));

        throw new AfterLastTestMethodFailedException($className, $raised);
    }

    /**
     * @param non-empty-string $id
     *
     * @throws PropertyDoesNotExistException
     *
     * @return array{property: PropertyTestSuite, test: TestMethod, testClass: TestSuite}
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
     * Remembers the error handlers that are registered as the ones of the
     * project, after hook methods of test classes were called: an error
     * handler that a hook method that is called before the first test of a
     * test class registers is registered for the examples of its properties,
     * until a hook method that is called after its last test removes it, as
     * in a test run.
     */
    private function rememberErrorHandlersOfProject(): void
    {
        $state = $this->stateOfCallerWhileCodeOfProjectRuns;

        assert($state !== null);

        $this->errorHandlersOfProject = self::removeErrorHandlersOfProject($state['marker']);

        // a hook method removed the marker: the error handlers of the caller
        // that are left are hidden below it again
        if (self::activeErrorHandler() !== $state['marker']) {
            set_error_handler($state['marker']);
        }

        set_error_handler(null);

        self::registerErrorHandlers($this->errorHandlersOfProject);
    }

    /**
     * Hides the error handlers of the caller, and registers the given ones of
     * the project.
     *
     * PHP has one stack of error handlers. The ones of the caller are left on
     * it, so that they keep the error levels that they were registered for,
     * which PHP does not tell, below two entries: a marker, and above it an
     * entry without an error handler. No error handler is active above this
     * entry, as when none is registered: PHPUnit's error handler, which
     * forwards an issue to the one that was active when it was registered,
     * has none to forward to, and PHPUnit, which removes the error handlers
     * that a test registered without removing them, finds none below it. The
     * marker, an error handler that handles nothing, tells that the error
     * handlers of the caller are still below it when the ones above it are
     * removed, also when code of the project removed one error handler more
     * than it registered.
     *
     * @param list<callable> $errorHandlersOfProject
     *
     * @return array{errorHandler: null|array<mixed>|callable|string, marker: Closure(): bool} the error handler of the caller that was active, and the marker
     */
    private static function hideErrorHandlersOfCaller(array $errorHandlersOfProject): array
    {
        $errorHandlersOfCaller = [
            'errorHandler' => self::activeErrorHandler(),
            'marker'       => static fn (): bool => false,
        ];

        set_error_handler($errorHandlersOfCaller['marker']);
        set_error_handler(null);

        self::registerErrorHandlers($errorHandlersOfProject);

        return $errorHandlersOfCaller;
    }

    /**
     * Removes the marker below which the error handlers of the caller are,
     * after the ones above it were removed, so that the error handler of the
     * caller that was active is active again.
     *
     * Code of the project that removed the marker also removed error handlers
     * of the caller. The one that was active is then registered again, for
     * all error levels, unless it can only be registered in the scope in
     * which it was registered.
     *
     * @param array{errorHandler: null|array<mixed>|callable|string, marker: Closure(): bool, ...} $errorHandlersOfCaller
     */
    private static function revealErrorHandlersOfCaller(array $errorHandlersOfCaller): void
    {
        if (self::activeErrorHandler() === $errorHandlersOfCaller['marker']) {
            restore_error_handler();
        }

        if (self::activeErrorHandler() === $errorHandlersOfCaller['errorHandler']) {
            return;
        }

        if ($errorHandlersOfCaller['errorHandler'] === null || is_callable($errorHandlersOfCaller['errorHandler'])) {
            set_error_handler($errorHandlersOfCaller['errorHandler']);
        }
    }

    /**
     * Removes the error handlers that are registered above the marker below
     * which the error handlers of the caller are, and returns them, from the
     * one that was registered first to the one that was registered last. The
     * marker is the active error handler afterwards.
     *
     * None is returned when code of the project removed the marker: the error
     * handlers that were removed cannot be told from the ones of the caller
     * then.
     *
     * An error handler that can only be called in the scope in which it was
     * registered, a private method, for instance, is left out, as it cannot
     * be registered again. As in a test run, such an error handler is removed
     * when the first test is run.
     *
     * @param Closure(): bool $marker
     *
     * @return list<callable>
     */
    private static function removeErrorHandlersOfProject(Closure $marker): array
    {
        $errorHandlers = [];

        while (true) {
            $errorHandler = self::activeErrorHandler();

            if ($errorHandler === null || $errorHandler === $marker) {
                break;
            }

            restore_error_handler();

            if (is_callable($errorHandler)) {
                $errorHandlers[] = $errorHandler;
            }
        }

        // the entry without an error handler above the marker
        if ($errorHandler === null) {
            restore_error_handler();
        }

        if (self::activeErrorHandler() !== $marker) {
            return [];
        }

        return array_reverse($errorHandlers);
    }

    /**
     * @param list<callable> $errorHandlers
     */
    private static function registerErrorHandlers(array $errorHandlers): void
    {
        foreach ($errorHandlers as $errorHandler) {
            set_error_handler($errorHandler);
        }
    }

    /**
     * The error handler that is active, which PHP only tells when another one
     * is set. It is not callable here when it can only be called in the scope
     * in which it was registered, a private method, for instance.
     *
     * @return null|array<mixed>|callable|string
     */
    private static function activeErrorHandler(): mixed
    {
        $handler = set_error_handler(static fn (): bool => false);

        restore_error_handler();

        return $handler;
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

        // nothing reads the telemetry of the events, which are neither printed
        // nor logged, and measuring it takes most of the time that emitting an
        // event takes
        EventFacade::instance()->stopMeasuringTelemetry();

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
     * The suite of a property is in the suite of its test class, as the tests
     * of the test methods of a test class are only built for the suite of the
     * test class, and the suite of a property is never in another suite of
     * its test method, for repeating it or retrying it, for instance.
     *
     * @return array<non-empty-string, array{property: PropertyTestSuite, test: TestMethod, testClass: TestSuite}>
     */
    private static function propertiesIn(TestSuite $testSuite): array
    {
        $properties = [];

        foreach ($testSuite as $test) {
            if ($test instanceof PropertyTestSuite) {
                assert($testSuite->isForTestClass());

                // a filter leaves the suite of a property in the test suite,
                // and leaves out the test of the property when it does not
                // match
                foreach ($test as $property) {
                    if ($property instanceof TestCase) {
                        $valueObject = $property->valueObjectForEvents();

                        $properties[$valueObject->id()] = [
                            'property'  => $test,
                            'test'      => $valueObject,
                            'testClass' => $testSuite,
                        ];
                    }
                }

                continue;
            }

            if ($test instanceof TestSuite) {
                // the properties of a nested test suite are added one at a
                // time, as merging the arrays takes time that grows
                // quadratically with the number of test classes
                foreach (self::propertiesIn($test) as $id => $property) {
                    $properties[$id] = $property;
                }
            }
        }

        return $properties;
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
                    SpanKind::Bytes              => 'bytes',
                    SpanKind::Characters         => 'characters',
                    SpanKind::Element            => 'element',
                    SpanKind::RequiredElement    => 'requiredelement',
                    SpanKind::Referable          => 'referable',
                    SpanKind::Reference          => 'reference',
                    SpanKind::ConsumingReference => 'consumingreference',
                    null                         => null,
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
