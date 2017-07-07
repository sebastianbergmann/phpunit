<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;

/**
 * Error handler that converts PHP errors and warnings to exceptions.
 */
class ErrorHandler
{
    protected static $errorStack = [];

    /**
     * Returns the error stack.
     *
     * @return array
     */
    public static function getErrorStack()
    {
        return self::$errorStack;
    }

    /**
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return false
     *
     * @throws Error
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!($errno & \error_reporting())) {
            return false;
        }

        self::$errorStack[] = [$errno, $errstr, $errfile, $errline];

        $trace = \debug_backtrace(false);
        \array_shift($trace);

        foreach ($trace as $frame) {
            if ($frame['function'] == '__toString') {
                return false;
            }
        }

        if ($errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_STRICT) {
            if (Notice::$enabled !== true) {
                return false;
            }

            $exception = Notice::class;
        } elseif ($errno == E_WARNING || $errno == E_USER_WARNING) {
            if (Warning::$enabled !== true) {
                return false;
            }

            $exception = Warning::class;
        } elseif ($errno == E_DEPRECATED || $errno == E_USER_DEPRECATED) {
            if (Deprecated::$enabled !== true) {
                return false;
            }

            $exception = Deprecated::class;
        } else {
            $exception = Error::class;
        }

        throw new $exception($errstr, $errno, $errfile, $errline);
    }

    /**
     * Registers an error handler and returns a function that will restore
     * the previous handler when invoked
     *
     * @param int $severity PHP predefined error constant
     *
     * @return \Closure
     *
     * @throws \Exception if event of specified severity is emitted
     */
    public static function handleErrorOnce($severity = E_WARNING)
    {
        $terminator = function () {
            static $expired = false;
            if (!$expired) {
                $expired = true;
                // cleans temporary error handler
                return \restore_error_handler();
            }
        };

        \set_error_handler(function ($errno, $errstr) use ($severity) {
            if ($errno === $severity) {
                return;
            }

            return false;
        });

        return $terminator;
    }
}
