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

use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ErrorHandler
{
    private static $errorStack = [];

    /**
     * Returns the error stack.
     */
    public static function getErrorStack(): array
    {
        return self::$errorStack;
    }

    /**
     * @throws \PHPUnit\Framework\Error\Deprecated
     * @throws \PHPUnit\Framework\Error\Error
     * @throws \PHPUnit\Framework\Error\Notice
     * @throws \PHPUnit\Framework\Error\Warning
     */
    public static function handleError(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        if (!($errorNumber & \error_reporting())) {
            return false;
        }

        self::$errorStack[] = [$errorNumber, $errorString, $errorFile, $errorLine];

        $trace = \debug_backtrace();
        \array_shift($trace);

        foreach ($trace as $frame) {
            if ($frame['function'] === '__toString') {
                return false;
            }
        }

        if ($errorNumber === \E_NOTICE || $errorNumber === \E_USER_NOTICE || $errorNumber === \E_STRICT) {
            if (Notice::$enabled !== true) {
                return false;
            }

            $exception = Notice::class;
        } elseif ($errorNumber === \E_WARNING || $errorNumber === \E_USER_WARNING) {
            if (Warning::$enabled !== true) {
                return false;
            }

            $exception = Warning::class;
        } elseif ($errorNumber === \E_DEPRECATED || $errorNumber === \E_USER_DEPRECATED) {
            if (Deprecated::$enabled !== true) {
                return false;
            }

            $exception = Deprecated::class;
        } else {
            $exception = Error::class;
        }

        throw new $exception($errorString, $errorNumber, $errorFile, $errorLine);
    }

    /**
     * Registers an error handler and returns a function that will restore
     * the previous handler when invoked
     *
     * @param int $severity PHP predefined error constant
     *
     * @throws \Exception if event of specified severity is emitted
     */
    public static function handleErrorOnce($severity = \E_WARNING): callable
    {
        $terminator = function () {
            static $expired = false;

            if (!$expired) {
                $expired = true;

                return \restore_error_handler();
            }
        };

        \set_error_handler(
            function ($errorNumber, $errorString) use ($severity) {
                if ($errorNumber === $severity) {
                    return;
                }

                return false;
            }
        );

        return $terminator;
    }
}
