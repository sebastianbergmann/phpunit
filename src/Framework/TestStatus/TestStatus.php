<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
abstract class TestStatus
{
    private string $message;

    public static function unknown(): self
    {
        return new Unknown;
    }

    public static function success(): self
    {
        return new Success;
    }

    public static function skipped(string $message = ''): self
    {
        return new Skipped($message);
    }

    public static function incomplete(string $message = ''): self
    {
        return new Incomplete($message);
    }

    public static function failure(string $message = ''): self
    {
        return new Failure($message);
    }

    public static function error(string $message = ''): self
    {
        return new Error($message);
    }

    public static function warning(string $message = ''): self
    {
        return new Warning($message);
    }

    public static function risky(string $message = ''): self
    {
        return new Risky($message);
    }

    private function __construct(string $message = '')
    {
        $this->message = $message;
    }

    public function isKnown(): bool
    {
        return false;
    }

    public function isUnknown(): bool
    {
        return false;
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function isSkipped(): bool
    {
        return false;
    }

    public function isIncomplete(): bool
    {
        return false;
    }

    public function isFailure(): bool
    {
        return false;
    }

    public function isError(): bool
    {
        return false;
    }

    public function isWarning(): bool
    {
        return false;
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function message(): string
    {
        return $this->message;
    }

    abstract public function type(): string;
}
