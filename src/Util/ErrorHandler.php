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
    private $errorStack = [];

    protected $convertWarning = true;
    protected $convertNotice = true;
    protected $convertDeprecated = true;


    protected function __construct()
    {

    }


    /**
     * @throws \PHPUnit\Framework\Error\Error
     */
    public function handleError(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        if (!($errorNumber & \error_reporting())) {
            return false;
        }

        $this->errorStack[] = [$errorNumber, $errorString, $errorFile, $errorLine];

        $trace = \debug_backtrace();
        \array_shift($trace);

        foreach ($trace as $frame) {
            if ($frame['function'] === '__toString') {
                return false;
            }
        }
        $exception = Error::class;
        if ($errorNumber === \E_NOTICE || $errorNumber === \E_USER_NOTICE || $errorNumber === \E_STRICT) {
            if(!$this->convertNotice) {
                return false;
            }
            $exception = Notice::class;
        }
        if ($errorNumber === \E_WARNING || $errorNumber === \E_USER_WARNING) {
            if(!$this->convertWarning) {
                return false;
            }
            $exception = Warning::class;

        }
        if ($errorNumber === \E_DEPRECATED || $errorNumber === \E_USER_DEPRECATED) {
            if(!$this->convertDeprecated) {
                return false;
            }
            $exception = Deprecated::class;

        }

        throw new $exception($errorString, 0, $errorNumber, $errorFile, $errorLine);
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

    /**
     * @param bool $convertError
     */
    public function setConvertError(bool $convertError): void
    {
        $this->convertError = $convertError;
    }

    /**
     * @return bool
     */
    public function getConvertWarning(): bool
    {
        return $this->convertWarning;
    }

    /**
     * @param bool $convertWarning
     */
    public function setConvertWarning(bool $convertWarning): void
    {
        $this->convertWarning = $convertWarning;
    }

    /**
     * @return bool
     */
    public function getConvertNotice(): bool
    {
        return $this->convertNotice;
    }

    /**
     * @param bool $convertNotice
     */
    public function setConvertNotice(bool $convertNotice): void
    {
        $this->convertNotice = $convertNotice;
    }

    /**
     * @return bool
     */
    public function getConvertDeprecated(): bool
    {
        return $this->convertDeprecated;
    }

    /**
     * @param bool $convertDeprecated
     */
    public function setConvertDeprecated(bool $convertDeprecated): void
    {
        $this->convertDeprecated = $convertDeprecated;
    }

    private static $instace;

    public static function getInstance()
    {
        if (self::$instace === null) {
            self::$instace = new self();
        }
        return self::$instace;
    }
}
