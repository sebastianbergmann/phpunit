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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
abstract class TestStatus
{
    private string $message;

    final public static function from(int $status): self
    {
        return match ($status) {
            0       => self::success(),
            1       => self::skipped(),
            2       => self::incomplete(),
            3       => self::notice(),
            4       => self::deprecation(),
            5       => self::risky(),
            6       => self::warning(),
            7       => self::failure(),
            8       => self::error(),
            default => self::unknown(),
        };
    }

    final public static function unknown(): self
    {
        return new Unknown;
    }

    final public static function success(): self
    {
        return new Success;
    }

    final public static function skipped(string $message = ''): self
    {
        return new Skipped($message);
    }

    final public static function incomplete(string $message = ''): self
    {
        return new Incomplete($message);
    }

    final public static function notice(string $message = ''): self
    {
        return new Notice($message);
    }

    final public static function deprecation(string $message = ''): self
    {
        return new Deprecation($message);
    }

    final public static function failure(string $message = ''): self
    {
        return new Failure($message);
    }

    final public static function error(string $message = ''): self
    {
        return new Error($message);
    }

    final public static function warning(string $message = ''): self
    {
        return new Warning($message);
    }

    final public static function risky(string $message = ''): self
    {
        return new Risky($message);
    }

    private function __construct(string $message = '')
    {
        $this->message = $message;
    }

    /**
     * @psalm-assert-if-true Known $this
     */
    final public function isKnown(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Unknown $this
     */
    final public function isUnknown(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Success $this
     */
    final public function isSuccess(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Skipped $this
     */
    final public function isSkipped(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Incomplete $this
     */
    final public function isIncomplete(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Notice $this
     */
    final public function isNotice(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Deprecation $this
     */
    final public function isDeprecation(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Failure $this
     */
    final public function isFailure(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Error $this
     */
    final public function isError(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Warning $this
     */
    final public function isWarning(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Risky $this
     */
    final public function isRisky(): bool
    {
        return false;
    }

    final public function message(): string
    {
        return $this->message;
    }

    final public function isMoreImportantThan(self $other): bool
    {
        return $this->asInt() > $other->asInt();
    }

    abstract public function asInt(): int;

    abstract public function asString(): string;
}
