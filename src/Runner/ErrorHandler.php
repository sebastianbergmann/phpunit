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
use function debug_backtrace;
use function error_reporting;
use function restore_error_handler;
use function set_error_handler;
use PHPUnit\Event;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Runner\Baseline\Baseline;
use PHPUnit\Runner\Baseline\Issue;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\Source;
use PHPUnit\TextUI\Configuration\SourceFilter;
use PHPUnit\Util\ExcludeList;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ErrorHandler
{
    private const UNHANDLEABLE_LEVELS         = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
    private const INSUPPRESSIBLE_LEVELS       = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
    private static ?self $instance            = null;
    private ?Baseline $baseline               = null;
    private bool $enabled                     = false;
    private ?int $originalErrorReportingLevel = null;
    private readonly Source $source;
    private readonly SourceFilter $sourceFilter;

    /**
     * @var array{functions: list<non-empty-string>, methods: list<array{className: class-string, methodName: non-empty-string}>}
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
     * @throws NoTestCaseObjectOnCallStackException
     */
    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        $suppressed = (error_reporting() & ~self::INSUPPRESSIBLE_LEVELS) === 0;

        if ($suppressed && (new ExcludeList)->isExcluded($errorFile)) {
            return false;
        }

        $test = Event\Code\TestMethodBuilder::fromCallStack();

        $ignoredByBaseline = $this->ignoredByBaseline($errorFile, $errorLine, $errorString);
        $ignoredByTest     = $test->metadata()->isIgnoreDeprecations()->isNotEmpty();

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
                    $this->trigger($test, false),
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
                    $this->trigger($test, true),
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

    public function enable(): void
    {
        if ($this->enabled) {
            return;
        }

        $oldErrorHandler = set_error_handler($this);

        if ($oldErrorHandler !== null) {
            restore_error_handler();

            return;
        }

        $this->enabled                     = true;
        $this->originalErrorReportingLevel = error_reporting();

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
    }

    public function useBaseline(Baseline $baseline): void
    {
        $this->baseline = $baseline;
    }

    /**
     * @param array{functions: list<non-empty-string>, methods: list<array{className: class-string, methodName: non-empty-string}>} $deprecationTriggers
     */
    public function useDeprecationTriggers(array $deprecationTriggers): void
    {
        $this->deprecationTriggers = $deprecationTriggers;
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

    private function trigger(TestMethod $test, bool $filterTrigger): IssueTrigger
    {
        if (!$this->source->notEmpty()) {
            return IssueTrigger::unknown();
        }

        $trace = $this->filteredStackTrace($filterTrigger);

        $triggeredInFirstPartyCode       = false;
        $triggerCalledFromFirstPartyCode = false;

        if (isset($trace[0]['file']) &&
            ($trace[0]['file'] === $test->file() ||
            $this->sourceFilter->includes($this->source, $trace[0]['file']))) {
            $triggeredInFirstPartyCode = true;
        }

        if (isset($trace[1]['file']) &&
            ($trace[1]['file'] === $test->file() ||
            $this->sourceFilter->includes($this->source, $trace[1]['file']))) {
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

    /**
     * @return list<array{file: string, line: int, class: string, function: string, type: string}>
     */
    private function filteredStackTrace(bool $filterDeprecationTriggers): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // self::filteredStackTrace(), self::trigger(), self::__invoke()
        unset($trace[0], $trace[1], $trace[2]);

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
                    /** @phpstan-ignore isset.offset */
                    isset($trace[$frame]['function']) &&
                    $trace[$frame]['function'] === $method['methodName']) {
                    unset($trace[$frame]);

                    continue 2;
                }
            }
        }

        return array_values($trace);
    }
}
