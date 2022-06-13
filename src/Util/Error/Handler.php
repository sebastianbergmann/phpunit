<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Error;

use const E_DEPRECATED;
use const E_NOTICE;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use function debug_backtrace;
use function error_reporting;
use function restore_error_handler;
use function set_error_handler;
use PHPUnit\Event;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Handler
{
    private static ?self $instance                = null;
    private bool $enabled                         = false;
    private bool $convertDeprecationsToExceptions = false;
    private bool $convertErrorsToExceptions       = false;
    private bool $convertNoticesToExceptions      = false;
    private bool $convertWarningsToExceptions     = false;
    private bool $ignoreWarnings                  = false;

    public static function instance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    /**
     * @throws Deprecation
     * @throws Error
     * @throws Exception
     * @throws Notice
     * @throws Warning
     */
    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        /*
         * Do not raise an exception when the error suppression operator (@) was used.
         *
         * @see https://github.com/sebastianbergmann/phpunit/issues/3739
         */
        if (!($errorNumber & error_reporting())) {
            return false;
        }

        switch ($errorNumber) {
            case E_NOTICE:
            case E_STRICT:
                Event\Facade::emitter()->testTriggeredPhpNotice(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertNoticesToExceptions) {
                    throw new Notice($errorString, $errorNumber, $errorFile, $errorLine);
                }

                return true;

            case E_USER_NOTICE:
                Event\Facade::emitter()->testTriggeredNotice(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertNoticesToExceptions) {
                    throw new Notice($errorString, $errorNumber, $errorFile, $errorLine);
                }

                break;

            case E_WARNING:
                if ($this->ignoreWarnings) {
                    return true;
                }

                Event\Facade::emitter()->testTriggeredPhpWarning(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertWarningsToExceptions) {
                    throw new Warning($errorString, $errorNumber, $errorFile, $errorLine);
                }

                break;

            case E_USER_WARNING:
                Event\Facade::emitter()->testTriggeredWarning(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertWarningsToExceptions) {
                    throw new Warning($errorString, $errorNumber, $errorFile, $errorLine);
                }

                break;

            case E_DEPRECATED:
                Event\Facade::emitter()->testTriggeredPhpDeprecation(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertDeprecationsToExceptions) {
                    throw new Deprecation($errorString, $errorNumber, $errorFile, $errorLine);
                }

                break;

            case E_USER_DEPRECATED:
                Event\Facade::emitter()->testTriggeredDeprecation(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertDeprecationsToExceptions) {
                    throw new Deprecation($errorString, $errorNumber, $errorFile, $errorLine);
                }

                break;

            case E_USER_ERROR:
                Event\Facade::emitter()->testTriggeredError(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertErrorsToExceptions) {
                    throw new Error($errorString, $errorNumber, $errorFile, $errorLine);
                }

                break;

            default:
                Event\Facade::emitter()->testTriggeredPhpError(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if ($this->convertErrorsToExceptions) {
                    throw new Error($errorString, $errorNumber, $errorFile, $errorLine);
                }
        }

        return true;
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

        $this->enabled = true;
    }

    public function disable(): void
    {
        if (!$this->enabled) {
            return;
        }

        restore_error_handler();

        $this->enabled = false;
    }

    public function isDisabled(): bool
    {
        return !$this->enabled;
    }

    public function convertDeprecationsToExceptions(): void
    {
        $this->convertDeprecationsToExceptions = true;
    }

    public function doNotConvertDeprecationsToExceptions(): void
    {
        $this->convertDeprecationsToExceptions = false;
    }

    public function convertErrorsToExceptions(): void
    {
        $this->convertErrorsToExceptions = true;
    }

    public function doNotConvertErrorsToExceptions(): void
    {
        $this->convertErrorsToExceptions = false;
    }

    public function convertNoticesToExceptions(): void
    {
        $this->convertNoticesToExceptions = true;
    }

    public function doNotConvertNoticesToExceptions(): void
    {
        $this->convertNoticesToExceptions = false;
    }

    public function convertWarningsToExceptions(): void
    {
        $this->convertWarningsToExceptions = true;
    }

    public function doNotConvertWarningsToExceptions(): void
    {
        $this->convertWarningsToExceptions = false;
    }

    public function ignoreWarnings(): void
    {
        $this->ignoreWarnings = true;
    }

    public function doNotIgnoreWarnings(): void
    {
        $this->ignoreWarnings = false;
    }

    /**
     * @throws Exception
     */
    private function testValueObjectForEvents(): Event\Code\Test
    {
        foreach (debug_backtrace() as $frame) {
            if (!isset($frame['object'])) {
                continue;
            }

            if (!$frame['object'] instanceof TestCase) {
                continue;
            }

            return $frame['object']->valueObjectForEvents();
        }

        throw new Exception('Cannot find TestCase object on call stack');
    }
}
