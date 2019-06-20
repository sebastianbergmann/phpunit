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

use PHPUnit\Framework\Error\Deprecation;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;

final class ErrorHandler
{
    /**
     * @var bool
     */
    private $convertDeprecationsToExceptions;

    /**
     * @var bool
     */
    private $convertErrorsToExceptions;

    /**
     * @var bool
     */
    private $convertNoticesToExceptions;

    /**
     * @var bool
     */
    private $convertWarningsToExceptions;

    /**
     * @var bool
     */
    private $registered = false;

    public function __construct(bool $convertDeprecationsToExceptions, bool $convertErrorsToExceptions, bool $convertNoticesToExceptions, bool $convertWarningsToExceptions)
    {
        $this->convertDeprecationsToExceptions = $convertDeprecationsToExceptions;
        $this->convertErrorsToExceptions       = $convertErrorsToExceptions;
        $this->convertNoticesToExceptions      = $convertNoticesToExceptions;
        $this->convertWarningsToExceptions     = $convertWarningsToExceptions;
    }

    public function __invoke(int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
    {
        switch ($errorNumber) {
            case \E_NOTICE:
            case \E_USER_NOTICE:
            case \E_STRICT:
                if (!$this->convertNoticesToExceptions) {
                    return false;
                }

                throw new Notice($errorString, $errorNumber, $errorFile, $errorLine);

            case \E_WARNING:
            case \E_USER_WARNING:
                if (!$this->convertWarningsToExceptions) {
                    return false;
                }

                throw new Warning($errorString, $errorNumber, $errorFile, $errorLine);

            case \E_DEPRECATED:
            case \E_USER_DEPRECATED:
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

        $oldErrorHandler = \set_error_handler($this);

        if ($oldErrorHandler !== null) {
            \restore_error_handler();

            return;
        }

        $this->registered = true;
    }

    public function unregister(): void
    {
        if (!$this->registered) {
            return;
        }

        \restore_error_handler();
    }
}
