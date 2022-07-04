<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestSize;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
abstract class TestSize
{
    final public static function unknown(): self
    {
        return new Unknown;
    }

    final public static function small(): self
    {
        return new Small;
    }

    final public static function medium(): self
    {
        return new Medium;
    }

    final public static function large(): self
    {
        return new Large;
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
     * @psalm-assert-if-true Small $this
     */
    final public function isSmall(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Medium $this
     */
    final public function isMedium(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Large $this
     */
    final public function isLarge(): bool
    {
        return false;
    }

    abstract public function asString(): string;
}
