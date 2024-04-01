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
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use function array_keys;
use function array_values;
use function assert;
use function debug_backtrace;
use function error_reporting;
use function restore_error_handler;
use function set_error_handler;
use PHPUnit\Event;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\NoDataProviderOnCallStackException;
use PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException;
use PHPUnit\Metadata\Api\DataProvider;
use PHPUnit\Runner\Baseline\Baseline;
use PHPUnit\Runner\Baseline\Issue;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\Source;
use PHPUnit\TextUI\Configuration\SourceFilter;
use PHPUnit\Util\ExcludeList;
use ReflectionClass;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ErrorHandler
{
    private const UNHANDLEABLE_LEVELS         = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
    private const INSUPPRESSIBLE_LEVELS       = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
    private static ?self $instance            = null;
    private ?Baseline $baseline               = null;
    private bool $enabledForDataProvider      = false;
    private bool $enabledForTest              = false;
    private ?int $originalErrorReportingLevel = null;
    private readonly Source $source;
    private readonly SourceFilter $sourceFilter;

    /**
     * @psalm-var array{functions: list<non-empty-string>, methods: list<array{className: class-string, methodName: non-empty-string}>}
     */
    private ?array $deprecationTriggers = null;

    public static function instance(): self
    {
        return self::$instance ?? self::$instance = new self(Registry::get()->source());
    }

    private function __construct(Source $source)
    {
        $this->source       = $source;
        $this->sourceFilter = new SourceFilter;
    }

    /**
     * @throws NoDataProviderOnCallStackException
     * @throws NoTestCaseObjectOnCallStackException
     */
    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        $suppressed = (error_reporting() & ~self::INSUPPRESSIBLE_LEVELS) === 0;

        if ($suppressed && (new ExcludeList)->isExcluded($errorFile)) {
            return false;
        }

        $ignoredByBaseline = $this->ignoredByBaseline($errorFile, $errorLine, $errorString);

        if ($this->enabledForTest) {
            return $this->processForTest($errorNumber, $errorString, $errorFile, $errorLine, $suppressed, $ignoredByBaseline);
        }

        if ($this->enabledForDataProvider) {
            return $this->processForDataProvider($errorNumber, $errorString, $errorFile, $errorLine, $suppressed, $ignoredByBaseline);
        }

        return false;
    }

    public function enableForDataProvider(): void
    {
        if (!$this->enable()) {
            return;
        }

        $this->enabledForDataProvider = true;
    }

    public function disableForDataProvider(): void
    {
        if (!$this->enabledForDataProvider) {
            return;
        }

        $this->enabledForDataProvider = false;

        $this->disable();
    }

    public function enableForTest(): void
    {
        if (!$this->enable()) {
            return;
        }

        $this->enabledForTest = true;
    }

    public function disableForTest(): void
    {
        if (!$this->enabledForTest) {
            return;
        }

        $this->enabledForTest = false;

        $this->disable();
    }

    public function useBaseline(Baseline $baseline): void
    {
        $this->baseline = $baseline;
    }

    /**
     * @psalm-param array{functions: list<non-empty-string>, methods: list<array{className: class-string, methodName: non-empty-string}>} $deprecationTriggers
     */
    public function useDeprecationTriggers(array $deprecationTriggers): void
    {
        $this->deprecationTriggers = $deprecationTriggers;
    }

    private function enable(): bool
    {
        if ($this->enabledForDataProvider || $this->enabledForTest) {
            return true;
        }

        $oldErrorHandler = set_error_handler($this);

        if ($oldErrorHandler !== null) {
            restore_error_handler();

            return false;
        }

        $this->originalErrorReportingLevel = error_reporting();

        error_reporting($this->originalErrorReportingLevel & self::UNHANDLEABLE_LEVELS);

        return true;
    }

    private function disable(): void
    {
        if ($this->enabledForDataProvider || $this->enabledForTest) {
            return;
        }

        restore_error_handler();

        error_reporting(error_reporting() | $this->originalErrorReportingLevel);

        $this->originalErrorReportingLevel = null;
    }

    /**
     * @throws NoTestCaseObjectOnCallStackException
     */
    private function processForTest(int $errorNumber, string $errorString, string $errorFile, int $errorLine, bool $suppressed, bool $ignoredByBaseline): bool
    {
        $test          = Event\Code\TestMethodBuilder::fromCallStack();
        $ignoredByTest = $test->metadata()->isIgnoreDeprecations()->isNotEmpty();

        switch ($errorNumber) {
            case E_NOTICE:
            case E_STRICT:
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
                    $this->trigger($test->file(), false),
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
                    $this->trigger($test->file(), true),
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
                return false;
        }

        return false;
    }

    /**
     * @throws NoDataProviderOnCallStackException
     */
    private function processForDataProvider(int $errorNumber, string $errorString, string $errorFile, int $errorLine, bool $suppressed, bool $ignoredByBaseline): bool
    {
        $dataProvider = $this->findDataProviderMethodOnCallStack();

        switch ($errorNumber) {
            case E_NOTICE:
            case E_STRICT:
                Event\Facade::emitter()->dataProviderTriggeredPhpNotice(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_USER_NOTICE:
                Event\Facade::emitter()->dataProviderTriggeredNotice(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_WARNING:
                Event\Facade::emitter()->dataProviderTriggeredPhpWarning(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_USER_WARNING:
                Event\Facade::emitter()->dataProviderTriggeredWarning(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                );

                break;

            case E_DEPRECATED:
                Event\Facade::emitter()->dataProviderTriggeredPhpDeprecation(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                    $this->trigger($dataProvider['file'], false),
                );

                break;

            case E_USER_DEPRECATED:
                Event\Facade::emitter()->dataProviderTriggeredDeprecation(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                    $ignoredByBaseline,
                    $this->trigger($dataProvider['file'], true),
                );

                break;

            case E_USER_ERROR:
                Event\Facade::emitter()->dataProviderTriggeredError(
                    $dataProvider['method'],
                    $errorString,
                    $errorFile,
                    $errorLine,
                    $suppressed,
                );

                throw new ErrorException('E_USER_ERROR was triggered');

            default:
                return false;
        }

        return false;
    }

    /**
     * @psalm-param non-empty-string $file
     * @psalm-param positive-int $line
     * @psalm-param non-empty-string $description
     */
    private function ignoredByBaseline(string $file, int $line, string $description): bool
    {
        if ($this->baseline === null) {
            return false;
        }

        return $this->baseline->has(Issue::from($file, $line, null, $description));
    }

    /**
     * @psalm-param non-empty-string $file
     */
    private function trigger(string $file, bool $filterTrigger): IssueTrigger
    {
        if (!$this->source->notEmpty()) {
            return IssueTrigger::unknown();
        }

        $trace = $this->filteredStackTrace($filterTrigger);

        // Data Provider methods are called via Reflection
        if (!isset($trace[1]['file'])) {
            $trace[1] = $trace[0];
        }

        assert(isset($trace[0]['file']));
        assert(isset($trace[1]['file']));

        $triggeredInFirstPartyCode       = false;
        $triggerCalledFromFirstPartyCode = false;

        if ($trace[0]['file'] === $file ||
            $this->sourceFilter->includes($this->source, $trace[0]['file'])) {
            $triggeredInFirstPartyCode = true;
        }

        if ($trace[1]['file'] === $file ||
            $this->sourceFilter->includes($this->source, $trace[1]['file'])) {
            $triggerCalledFromFirstPartyCode = true;
        }

        if ($triggerCalledFromFirstPartyCode) {
            if ($triggeredInFirstPartyCode) {
                return IssueTrigger::self();
            }

            return IssueTrigger::direct();
        }

        return IssueTrigger::indirect();
    }

    private function filteredStackTrace(bool $filterDeprecationTriggers): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // self::filteredStackTrace(), self::trigger(), self::processForTest(), self::__invoke()
        unset($trace[0], $trace[1], $trace[2], $trace[3]);

        if ($this->deprecationTriggers === null || !$filterDeprecationTriggers) {
            return array_values($trace);
        }

        foreach (array_keys($trace) as $frame) {
            foreach ($this->deprecationTriggers['functions'] as $function) {
                if (!isset($trace[$frame]['class']) &&
                    isset($trace[$frame]['function']) &&
                    $trace[$frame]['function'] === $function) {
                    unset($trace[$frame]);

                    continue 2;
                }
            }

            foreach ($this->deprecationTriggers['methods'] as $method) {
                if (isset($trace[$frame]['class']) &&
                    $trace[$frame]['class'] === $method['className'] &&
                    isset($trace[$frame]['function']) &&
                    $trace[$frame]['function'] === $method['methodName']) {
                    unset($trace[$frame]);

                    continue 2;
                }
            }
        }

        return array_values($trace);
    }

    /**
     * @psalm-return array{file: non-empty-string, method: ClassMethod}
     *
     * @throws NoDataProviderOnCallStackException
     */
    private function findDataProviderMethodOnCallStack(): array
    {
        $trace             = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $dataProviderFrame = false;

        foreach (array_keys($trace) as $frame) {
            if (!isset($trace[$frame]['class'])) {
                continue;
            }

            if ($trace[$frame]['class'] === DataProvider::class) {
                // PHPUnit\Metadata\Api\DataProvider::dataProvidedByMethods(), ReflectionMethod::invoke()
                $dataProviderFrame = $frame - 2;

                break;
            }
        }

        if ($dataProviderFrame === false) {
            throw new NoDataProviderOnCallStackException;
        }

        assert(isset($trace[$dataProviderFrame]));
        assert(isset($trace[$dataProviderFrame]['class']));
        assert(isset($trace[$dataProviderFrame]['function']));
        assert($trace[$dataProviderFrame]['function'] !== '');

        /** @noinspection PhpUnhandledExceptionInspection */
        return [
            'file'   => (new ReflectionClass($trace[$dataProviderFrame]['class']))->getFileName(),
            'method' => new ClassMethod(
                $trace[$dataProviderFrame]['class'],
                $trace[$dataProviderFrame]['function'],
            ),
        ];
    }
}
