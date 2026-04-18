<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const E_COMPILE_ERROR;
use const E_COMPILE_WARNING;
use const E_CORE_ERROR;
use const E_CORE_WARNING;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use function array_keys;
use function array_reverse;
use function array_slice;
use function array_unshift;
use function array_values;
use function assert;
use function count;
use function debug_backtrace;
use function defined;
use function error_reporting;
use function is_callable;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use PHPUnit\Event;
use PHPUnit\Event\Code\IssueTrigger\Code;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\IgnoreDeprecations;
use PHPUnit\Metadata\Parser\Registry as MetadataParserRegistry;
use PHPUnit\Runner\Baseline\Baseline;
use PHPUnit\Runner\Baseline\Issue;
use PHPUnit\Runner\IssueTriggerResolver\DefaultResolver as DefaultIssueTriggerResolver;
use PHPUnit\Runner\IssueTriggerResolver\Resolver as IssueTriggerResolver;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\SourceFilter;
use PHPUnit\Util\ExcludeList;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-type DeprecationMethod array{className: class-string, methodName: non-empty-string}
 * @phpstan-type DeprecationTriggers array{functions: list<non-empty-string>, methods: list<DeprecationMethod>}
 * @phpstan-type StackFrame array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}
 */
final class ErrorHandler
{
    private const int UNHANDLEABLE_LEVELS   = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
    private const int INSUPPRESSIBLE_LEVELS = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
    private static ?self $instance          = null;
    private ?Baseline $baseline             = null;
    private ExcludeList $excludeList;
    private bool $enabled                          = false;
    private ?int $originalErrorReportingLevel      = null;
    private mixed $previousErrorHandler            = null;
    private mixed $previousNonTestCaseErrorHandler = null;
    private readonly bool $identifyIssueTrigger;

    /**
     * @var array<string, list<array{int, string, string, int}>>
     */
    private array $testCaseContextIssues = [];
    private ?string $testCaseContext     = null;

    /**
     * @var ?list<callable>
     */
    private ?array $backupErrorHandlers = null;

    /**
     * @var ?DeprecationTriggers
     */
    private ?array $deprecationTriggers = null;

    /**
     * @var non-empty-list<IssueTriggerResolver>
     */
    private array $issueTriggerResolvers;

    public static function instance(): self
    {
        $source = ConfigurationRegistry::get()->source();

        $identifyIssueTrigger = true;

        if (!$source->identifyIssueTrigger()) {
            $identifyIssueTrigger = false;
        }

        if (!$source->notEmpty()) {
            $identifyIssueTrigger = false;
        }

        return self::$instance ?? self::$instance = new self($identifyIssueTrigger);
    }

    private function __construct(bool $identifyIssueTrigger)
    {
        $this->excludeList           = new ExcludeList;
        $this->identifyIssueTrigger  = $identifyIssueTrigger;
        $this->issueTriggerResolvers = [new DefaultIssueTriggerResolver];
    }

    /**
     * @throws NoTestCaseObjectOnCallStackException
     */
    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        $suppressed = (error_reporting() & ~self::INSUPPRESSIBLE_LEVELS) === 0;

        if ($suppressed && $this->excludeList->isExcluded($errorFile)) {
            // @codeCoverageIgnoreStart
            return $this->forwardToPreviousErrorHandler($errorNumber, $errorString, $errorFile, $errorLine);
            // @codeCoverageIgnoreEnd
        }

        /**
         * E_STRICT is deprecated since PHP 8.4.
         *
         * @see https://github.com/sebastianbergmann/phpunit/issues/5956
         */
        if (defined('E_STRICT') && $errorNumber === 2048) {
            // @codeCoverageIgnoreStart
            $errorNumber = E_NOTICE;
            // @codeCoverageIgnoreEnd
        }

        $test = Event\Code\TestMethodBuilder::fromCallStack();

        if ($errorNumber === E_USER_DEPRECATED) {
            $deprecationFrame = $this->guessDeprecationFrame();
            $errorFile        = $deprecationFrame['file'] ?? $errorFile;
            $errorLine        = $deprecationFrame['line'] ?? $errorLine;
        }

        $ignoredByBaseline = $this->ignoredByBaseline($errorFile, $errorLine, $errorString);
        $ignoredByTest     = $this->deprecationIgnoredByTest($test, $errorString);

        switch ($errorNumber) {
            case E_NOTICE:
                Event\Facade::emitter()->testTriggeredPhpNotice(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_USER_NOTICE:
                Event\Facade::emitter()->testTriggeredNotice(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_WARNING:
                Event\Facade::emitter()->testTriggeredPhpWarning(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_USER_WARNING:
                Event\Facade::emitter()->testTriggeredWarning(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_DEPRECATED:
                Event\Facade::emitter()->testTriggeredPhpDeprecation(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                    $ignoredByTest,
                    $this->trigger($test, false, $errorString, $errorFile),
                );

                break;

            case E_USER_DEPRECATED:
                Event\Facade::emitter()->testTriggeredDeprecation(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                    $ignoredByTest,
                    $this->trigger($test, true, $errorString),
                    $this->stackTrace(),
                );

                break;

            case E_USER_ERROR:
                Event\Facade::emitter()->testTriggeredError(
                    $test,
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                );

                throw new ErrorException('E_USER_ERROR was triggered');

            default:
                return $this->forwardToPreviousErrorHandler($errorNumber, $errorString, $errorFile, $errorLine);
        }

        return $this->forwardToPreviousErrorHandler($errorNumber, $errorString, $errorFile, $errorLine);
    }

    public function handleNonTestCaseIssue(int $errorNumber, string $errorString, string $errorFile, int $errorLine): true
    {
        $suppressed = (error_reporting() & ~self::INSUPPRESSIBLE_LEVELS) === 0;

        if ($suppressed && $this->excludeList->isExcluded($errorFile)) {
            return true;
        }

        if ($this->testCaseContext !== null) {
            $this->testCaseContextIssues[$this->testCaseContext][] = [$errorNumber, $errorString, $errorFile, $errorLine];

            return true;
        }

        /**
         * E_STRICT is deprecated since PHP 8.4.
         *
         * @see https://github.com/sebastianbergmann/phpunit/issues/5956
         */
        if (defined('E_STRICT') && $errorNumber === 2048) {
            // @codeCoverageIgnoreStart
            $errorNumber = E_NOTICE;
            // @codeCoverageIgnoreEnd
        }

        if ($errorNumber === E_USER_DEPRECATED) {
            $deprecationFrame = $this->guessDeprecationFrame();
            $errorFile        = $deprecationFrame['file'] ?? $errorFile;
            $errorLine        = $deprecationFrame['line'] ?? $errorLine;
        }

        $ignoredByBaseline = $this->ignoredByBaseline($errorFile, $errorLine, $errorString);

        switch ($errorNumber) {
            case E_NOTICE:
                Event\Facade::emitter()->testRunnerTriggeredPhpNotice(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_USER_NOTICE:
                Event\Facade::emitter()->testRunnerTriggeredNotice(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_WARNING:
                Event\Facade::emitter()->testRunnerTriggeredPhpWarning(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_USER_WARNING:
                Event\Facade::emitter()->testRunnerTriggeredWarning(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_DEPRECATED:
                Event\Facade::emitter()->testRunnerTriggeredPhpDeprecation(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                    $this->triggerWithoutTest(false, $errorString, $errorFile),
                );

                break;

            case E_USER_DEPRECATED:
                Event\Facade::emitter()->testRunnerTriggeredDeprecation(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                    $this->triggerWithoutTest(true, $errorString),
                    $this->stackTrace(),
                );

                break;

            case E_USER_ERROR:
                Event\Facade::emitter()->testRunnerTriggeredError(
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                );

                break;
        }

        if ($this->previousNonTestCaseErrorHandler !== null) {
            ($this->previousNonTestCaseErrorHandler)($errorNumber, $errorString, $errorFile, $errorLine);
        }

        return true;
    }

    public function registerForNonTestCaseContext(): void
    {
        $previousHandler = set_error_handler(
            [self::$instance, 'handleNonTestCaseIssue'],
            E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE | E_WARNING | E_USER_WARNING,
        );

        if ($previousHandler !== null) {
            $this->previousNonTestCaseErrorHandler = $previousHandler;
        }
    }

    public function restoreForNonTestCaseContext(): void
    {
        restore_error_handler();

        $this->previousNonTestCaseErrorHandler = null;
    }

    public function enable(TestCase $test): void
    {
        assert(!$this->enabled);

        $previousErrorHandler = set_error_handler($this);

        if ($previousErrorHandler !== null) {
            $this->previousErrorHandler = $previousErrorHandler;
        }

        $this->enabled                     = true;
        $this->originalErrorReportingLevel = error_reporting();

        $this->triggerTestCaseContextIssues($test);

        error_reporting($this->originalErrorReportingLevel & self::UNHANDLEABLE_LEVELS);
    }

    public function disable(): void
    {
        if (!$this->enabled) {
            return;
        }

        restore_error_handler();

        error_reporting(error_reporting() | $this->originalErrorReportingLevel);

        $this->enabled                     = false;
        $this->originalErrorReportingLevel = null;
        $this->previousErrorHandler        = null;
    }

    /**
     * @return list<string>
     */
    public function snapshotErrorHandlers(): array
    {
        $messages = [];

        $this->backupErrorHandlers = $this->activeErrorHandlers($messages);

        return $messages;
    }

    /**
     * @return list<string>
     */
    public function restoreErrorHandlers(bool $inIsolation): array
    {
        $messages            = [];
        $activeErrorHandlers = $this->activeErrorHandlers($messages);

        $activeAbove = $this->handlersAboveSelf($activeErrorHandlers);
        $backupAbove = $this->handlersAboveSelf($this->backupErrorHandlers);

        if ($this->isOnStack($this->backupErrorHandlers) &&
            !$this->isOnStack($activeErrorHandlers)) {
            $messages[] = 'Test code or tested code removed error handlers other than its own';
        } elseif ($activeAbove !== $backupAbove) {
            if (count($activeAbove) > count($backupAbove)) {
                if (!$inIsolation) {
                    $messages[] = 'Test code or tested code did not remove its own error handlers';
                }
            } else {
                $messages[] = 'Test code or tested code removed error handlers other than its own';
            }
        }

        if ($activeErrorHandlers !== $this->backupErrorHandlers) {
            foreach ($activeErrorHandlers as $handler) {
                restore_error_handler();
            }

            foreach ($this->backupErrorHandlers as $handler) {
                set_error_handler($handler);
            }
        }

        $this->backupErrorHandlers = null;

        return $messages;
    }

    public function useBaseline(Baseline $baseline): void
    {
        $this->baseline = $baseline;
    }

    /**
     * @param DeprecationTriggers $deprecationTriggers
     */
    public function useDeprecationTriggers(array $deprecationTriggers): void
    {
        $this->deprecationTriggers = $deprecationTriggers;
    }

    public function addIssueTriggerResolver(IssueTriggerResolver $resolver): void
    {
        array_unshift($this->issueTriggerResolvers, $resolver);
    }

    public function enterTestCaseContext(string $className, string $methodName): void
    {
        $this->testCaseContext = $this->testCaseContext($className, $methodName);
    }

    public function leaveTestCaseContext(): void
    {
        $this->testCaseContext = null;
    }

    /**
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $description
     */
    private function ignoredByBaseline(string $file, int $line, string $description): bool
    {
        if ($this->baseline === null) {
            return false;
        }

        return $this->baseline->has(Issue::from($file, $line, null, $description));
    }

    /**
     * @param null|non-empty-string $errorFile
     */
    private function triggerWithoutTest(bool $isUserland, string $errorString, ?string $errorFile = null): IssueTrigger
    {
        if (!$this->identifyIssueTrigger) {
            return IssueTrigger::from(null, null);
        }

        if (!$isUserland) {
            assert($errorFile !== null);

            return IssueTrigger::from(Code::PHP, $this->categorizeFileWithoutTest($errorFile));
        }

        $trace = $this->filteredStackTrace();

        return $this->triggerForUserlandDeprecationWithoutTest($errorString, $trace);
    }

    /**
     * @param null|non-empty-string $errorFile
     */
    private function trigger(TestMethod $test, bool $isUserland, string $errorString, ?string $errorFile = null): IssueTrigger
    {
        if (!$this->identifyIssueTrigger) {
            return IssueTrigger::from(null, null);
        }

        if (!$isUserland) {
            assert($errorFile !== null);

            return IssueTrigger::from(Code::PHP, $this->categorizeFile($errorFile, $test));
        }

        $trace = $this->filteredStackTrace();

        return $this->triggerForUserlandDeprecation($test, $errorString, $trace);
    }

    /**
     * @param list<array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}> $trace
     */
    private function triggerForUserlandDeprecation(TestMethod $test, string $message, array $trace): IssueTrigger
    {
        foreach ($this->issueTriggerResolvers as $resolver) {
            $result = $resolver->resolve($trace, $message);

            if ($result === null) {
                continue;
            }

            $callee = null;

            if ($result->hasCallee()) {
                $callee = $this->categorizeFile($result->callee(), $test);
            }

            $caller = null;

            if ($result->hasCaller()) {
                $caller = $this->categorizeFile($result->caller(), $test);
            }

            return IssueTrigger::from($callee, $caller);
        }

        // @codeCoverageIgnoreStart
        return IssueTrigger::from(null, null);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param non-empty-string $file
     */
    private function categorizeFile(string $file, TestMethod $test): Code
    {
        if ($file === $test->file()) {
            return Code::Test;
        }

        if (SourceFilter::instance()->includes($file)) {
            return Code::FirstParty;
        }

        if ($this->excludeList->isExcluded($file)) {
            return Code::PHPUnit;
        }

        return Code::ThirdParty;
    }

    /**
     * @param non-empty-string $file
     */
    private function categorizeFileWithoutTest(string $file): Code
    {
        if (SourceFilter::instance()->includes($file)) {
            return Code::FirstParty;
        }

        if ($this->excludeList->isExcluded($file)) {
            return Code::PHPUnit;
        }

        return Code::ThirdParty;
    }

    /**
     * @param list<array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}> $trace
     */
    private function triggerForUserlandDeprecationWithoutTest(string $message, array $trace): IssueTrigger
    {
        foreach ($this->issueTriggerResolvers as $resolver) {
            $result = $resolver->resolve($trace, $message);

            if ($result === null) {
                continue;
            }

            $callee = null;

            if ($result->hasCallee()) {
                $callee = $this->categorizeFileWithoutTest($result->callee());
            }

            $caller = null;

            if ($result->hasCaller()) {
                $caller = $this->categorizeFileWithoutTest($result->caller());
            }

            return IssueTrigger::from($callee, $caller);
        }

        // @codeCoverageIgnoreStart
        return IssueTrigger::from(null, null);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return list<array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}>
     */
    private function filteredStackTrace(): array
    {
        $ignoreArguments = count($this->issueTriggerResolvers) === 1;

        $trace = $this->errorStackTrace($ignoreArguments);

        if ($this->deprecationTriggers === null) {
            return array_values($trace);
        }

        foreach (array_keys($trace) as $frame) {
            foreach ($this->deprecationTriggers['functions'] as $function) {
                if ($this->frameIsFunction($trace[$frame], $function)) {
                    unset($trace[$frame]);

                    continue 2;
                }
            }

            foreach ($this->deprecationTriggers['methods'] as $method) {
                if ($this->frameIsMethod($trace[$frame], $method)) {
                    unset($trace[$frame]);

                    continue 2;
                }
            }
        }

        return array_values($trace);
    }

    /**
     * @return ?array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}
     */
    private function guessDeprecationFrame(): ?array
    {
        if ($this->deprecationTriggers === null) {
            return null;
        }

        $trace = $this->errorStackTrace();

        foreach ($trace as $frame) {
            foreach ($this->deprecationTriggers['functions'] as $function) {
                if ($this->frameIsFunction($frame, $function)) {
                    return $frame;
                }
            }

            foreach ($this->deprecationTriggers['methods'] as $method) {
                if ($this->frameIsMethod($frame, $method)) {
                    return $frame;
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}>
     */
    private function errorStackTrace(bool $ignoreArgs = true): array
    {
        if ($ignoreArgs) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $trace = debug_backtrace();
        }

        $i = 0;

        do {
            unset($trace[$i]);
        } while (self::class === ($trace[++$i]['class'] ?? null));

        return array_values($trace);
    }

    /**
     * @param StackFrame       $frame
     * @param non-empty-string $function
     */
    private function frameIsFunction(array $frame, string $function): bool
    {
        return !isset($frame['class']) && isset($frame['function']) && $frame['function'] === $function;
    }

    /**
     * @param StackFrame        $frame
     * @param DeprecationMethod $method
     */
    private function frameIsMethod(array $frame, array $method): bool
    {
        return isset($frame['class']) &&
            $frame['class'] === $method['className'] &&
            isset($frame['function']) &&
            $frame['function'] === $method['methodName'];
    }

    private function stackTrace(): string
    {
        $buffer = '';

        foreach ($this->errorStackTrace() as $frame) {
            /**
             * @see https://github.com/sebastianbergmann/phpunit/issues/6043
             */
            if (!isset($frame['file'])) {
                continue;
            }

            if ($this->excludeList->isExcluded($frame['file'])) {
                continue;
            }

            $buffer .= sprintf(
                "%s:%s\n",
                $frame['file'],
                $frame['line'] ?? '?',
            );
        }

        return $buffer;
    }

    private function triggerTestCaseContextIssues(TestCase $test): void
    {
        $testCaseContext = $this->testCaseContext($test::class, $test->name());

        foreach ($this->testCaseContextIssues[$testCaseContext] ?? [] as $d) {
            $this->__invoke(...$d);
        }
    }

    private function testCaseContext(string $className, string $methodName): string
    {
        return "{$className}::{$methodName}";
    }

    /**
     * @param list<string> $messages
     *
     * @return list<callable>
     */
    private function activeErrorHandlers(array &$messages = []): array
    {
        $activeErrorHandlers = [];

        while (true) {
            $previousHandler = set_error_handler(static fn () => false);

            restore_error_handler();

            if ($previousHandler === null) {
                break;
            }

            $activeErrorHandlers[] = $previousHandler;

            restore_error_handler();
        }

        $activeErrorHandlers      = array_reverse($activeErrorHandlers);
        $invalidErrorHandlerStack = false;

        foreach ($activeErrorHandlers as $handler) {
            if (!is_callable($handler)) {
                $invalidErrorHandlerStack = true;

                continue;
            }

            set_error_handler($handler);
        }

        if ($invalidErrorHandlerStack) {
            $messages[] = 'At least one error handler is not callable outside the scope it was registered in';
        }

        return $activeErrorHandlers;
    }

    /**
     * @param list<callable> $handlers
     *
     * @return list<callable>
     */
    private function handlersAboveSelf(array $handlers): array
    {
        $position = null;

        foreach ($handlers as $i => $handler) {
            if ($handler instanceof self) {
                $position = $i;

                break;
            }
        }

        if ($position === null) {
            return $handlers;
        }

        return array_slice($handlers, $position + 1);
    }

    /**
     * @param list<callable> $handlers
     */
    private function isOnStack(array $handlers): bool
    {
        foreach ($handlers as $handler) {
            if ($handler instanceof self) {
                return true;
            }
        }

        return false;
    }

    private function deprecationIgnoredByTest(TestMethod $test, string $message): bool
    {
        $metadata = MetadataParserRegistry::parser()->forClassAndMethod($test->className(), $test->methodName())->isIgnoreDeprecations()->asArray();

        foreach ($metadata as $metadatum) {
            assert($metadatum instanceof IgnoreDeprecations);

            $ignoreDeprecationMessagePattern = $metadatum->messagePattern();

            if ($ignoreDeprecationMessagePattern === null ||
                (bool) preg_match('{' . $ignoreDeprecationMessagePattern . '}', $message)) {
                return true;
            }
        }

        return false;
    }

    private function forwardToPreviousErrorHandler(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        if ($this->previousErrorHandler === null) {
            return false;
        }

        return (bool) ($this->previousErrorHandler)($errorNumber, $errorString, $errorFile, $errorLine);
    }
}
