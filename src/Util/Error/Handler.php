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
    private bool $convertDeprecationsToExceptions;
    private bool $convertErrorsToExceptions;
    private bool $convertNoticesToExceptions;
    private bool $convertWarningsToExceptions;
    private bool $registered = false;

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

    public function __construct(bool $convertDeprecationsToExceptions, bool $convertErrorsToExceptions, bool $convertNoticesToExceptions, bool $convertWarningsToExceptions)
    {
        $this->convertDeprecationsToExceptions = $convertDeprecationsToExceptions;
        $this->convertErrorsToExceptions       = $convertErrorsToExceptions;
        $this->convertNoticesToExceptions      = $convertNoticesToExceptions;
        $this->convertWarningsToExceptions     = $convertWarningsToExceptions;
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
            case E_USER_NOTICE:
            case E_STRICT:
                if (!$this->convertNoticesToExceptions) {
                    return false;
                }

                throw new Notice($errorString, $errorNumber, $errorFile, $errorLine);

            case E_WARNING:
            case E_USER_WARNING:
                if (!$this->convertWarningsToExceptions) {
                    return false;
                }

                throw new Warning($errorString, $errorNumber, $errorFile, $errorLine);

            case E_DEPRECATED:
                Event\Facade::emitter()->testUsedDeprecatedPhpFeature(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if (!$this->convertDeprecationsToExceptions) {
                    return false;
                }

                throw new Deprecation($errorString, $errorNumber, $errorFile, $errorLine);

            case E_USER_DEPRECATED:
                Event\Facade::emitter()->testUsedDeprecatedFeature(
                    $this->testValueObjectForEvents(),
                    $errorString,
                    $errorFile,
                    $errorLine
                );

                if (!$this->convertDeprecationsToExceptions) {
                    return false;
                }

                throw new Deprecation($errorString, $errorNumber, $errorFile, $errorLine);

            default:
                if (!$this->convertErrorsToExceptions) {
                    return false;
                }

                throw new Error($errorString, $errorNumber, $errorFile, $errorLine);
        }
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $oldErrorHandler = set_error_handler($this);

        if ($oldErrorHandler !== null) {
            restore_error_handler();

            return;
        }

        $this->registered = true;
    }

    public function unregister(): void
    {
        if (!$this->registered) {
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
}
