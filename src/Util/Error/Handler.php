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
    private static ?self $instance = null;
    private bool $enabled          = false;

    public static function activate(): void
    {
        self::instance()->enable();
    }

    public static function deactivate(): void
    {
        self::instance()->enable();
    }

    public static function invokeIgnoringWarnings(callable $callable): mixed
    {
        set_error_handler(
            static function ($errorNumber)
            {
                if ($errorNumber === E_WARNING) {
                    return null;
                }

                return false;
            }
        );

        $result = $callable();

        restore_error_handler();

        return $result;
    }

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

                throw new Notice($errorString, $errorNumber, $errorFile, $errorLine);

            case E_USER_NOTICE:
                Event\Facade::emitter()->testTriggeredNotice(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                throw new Notice($errorString, $errorNumber, $errorFile, $errorLine);

            case E_WARNING:
                Event\Facade::emitter()->testTriggeredPhpWarning(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                throw new Warning($errorString, $errorNumber, $errorFile, $errorLine);

            case E_USER_WARNING:
                Event\Facade::emitter()->testTriggeredWarning(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                throw new Warning($errorString, $errorNumber, $errorFile, $errorLine);

            case E_DEPRECATED:
                Event\Facade::emitter()->testUsedDeprecatedPhpFeature(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                throw new Deprecation($errorString, $errorNumber, $errorFile, $errorLine);

            case E_USER_DEPRECATED:
                Event\Facade::emitter()->testUsedDeprecatedFeature(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                throw new Deprecation($errorString, $errorNumber, $errorFile, $errorLine);

            default:
                throw new Error($errorString, $errorNumber, $errorFile, $errorLine);
        }
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

    private static function instance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }
}
