<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const E_DEPRECATED;
use const E_NOTICE;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use function debug_backtrace;
use function error_reporting;
use function in_array;
use function restore_error_handler;
use function set_error_handler;
use PHPUnit\Event;
use PHPUnit\Framework\TestCase;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ErrorHandler
{
    private static ?self $instance = null;
    private bool $enabled          = false;

    /**
     * @psalm-var array<int, array<string, true>>
     */
    private array $includeDirectories = [
        E_DEPRECATED      => [],
        E_NOTICE          => [],
        E_WARNING         => [],
        E_USER_DEPRECATED => [],
        E_USER_NOTICE     => [],
        E_USER_WARNING    => [],
    ];

    public static function instance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    /**
     * @throws Exception
     */
    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        $suppressed = !($errorNumber & error_reporting());

        if ($suppressed &&
            in_array($errorNumber, [E_DEPRECATED, E_NOTICE, E_STRICT, E_WARNING], true)) {
            return false;
        }

        switch ($errorNumber) {
            case E_NOTICE:
            case E_STRICT:
                if (!$this->shouldEventBeEmitted(E_NOTICE, $errorFile)) {
                    return true;
                }

                Event\Facade::emitter()->testTriggeredPhpNotice(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                return true;

            case E_USER_NOTICE:
                if (!$this->shouldEventBeEmitted(E_USER_NOTICE, $errorFile)) {
                    break;
                }

                Event\Facade::emitter()->testTriggeredNotice(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                break;

            case E_WARNING:
                if (!$this->shouldEventBeEmitted(E_WARNING, $errorFile)) {
                    break;
                }

                Event\Facade::emitter()->testTriggeredPhpWarning(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                break;

            case E_USER_WARNING:
                if (!$this->shouldEventBeEmitted(E_USER_WARNING, $errorFile)) {
                    break;
                }

                Event\Facade::emitter()->testTriggeredWarning(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                break;

            case E_DEPRECATED:
                if (!$this->shouldEventBeEmitted(E_DEPRECATED, $errorFile)) {
                    break;
                }

                Event\Facade::emitter()->testTriggeredPhpDeprecation(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                break;

            case E_USER_DEPRECATED:
                if (!$this->shouldEventBeEmitted(E_USER_DEPRECATED, $errorFile)) {
                    break;
                }

                Event\Facade::emitter()->testTriggeredDeprecation(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                break;

            case E_USER_ERROR:
                Event\Facade::emitter()->testTriggeredError(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                break;

            default:
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
        }

        return true;
    }

    public function enable(): void
    {
        if ($this->enabled) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $oldErrorHandler = set_error_handler($this);

        if ($oldErrorHandler !== null) {
            // @codeCoverageIgnoreStart
            restore_error_handler();

            return;
            // @codeCoverageIgnoreEnd
        }

        $this->enabled = true;
    }

    public function disable(): void
    {
        if (!$this->enabled) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        restore_error_handler();

        $this->enabled = false;
    }

    public function includeDirectory(int $errorNumber, string $directory): void
    {
        $this->includeDirectories[$errorNumber][$directory] = true;
    }

    private function shouldEventBeEmitted(int $errorNumber, string $errorFile): bool
    {
        if (empty($this->includeDirectories[$errorNumber])) {
            return true;
        }

        if (isset($this->includeDirectories[$errorNumber][$errorFile])) {
            return true;
        }

        return false;
    }

    /**
     * @throws NoTestCaseObjectOnCallStackException
     */
    private function testValueObjectForEvents(): Event\Code\Test
    {
        foreach (debug_backtrace() as $frame) {
            if (isset($frame['object']) && $frame['object'] instanceof TestCase) {
                return $frame['object']->valueObjectForEvents();
            }
        }

        // @codeCoverageIgnoreStart
        throw new NoTestCaseObjectOnCallStackException;
        // @codeCoverageIgnoreEnd
    }
}
