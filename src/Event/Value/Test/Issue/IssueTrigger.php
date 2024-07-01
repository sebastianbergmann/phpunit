<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code\IssueTrigger;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class IssueTrigger
{
    public static function self(): SelfTrigger
    {
        return new SelfTrigger;
    }

    public static function direct(): DirectTrigger
    {
        return new DirectTrigger;
    }

    public static function indirect(): IndirectTrigger
    {
        return new IndirectTrigger;
    }

    public static function unknown(): UnknownTrigger
    {
        return new UnknownTrigger;
    }

    final private function __construct()
    {
    }

    /**
     * Your own code triggers an issue in your own code.
     *
     * @phpstan-assert-if-true SelfTrigger $this
     */
    public function isSelf(): bool
    {
        return false;
    }

    /**
     * Your own code triggers an issue in third-party code.
     *
     * @phpstan-assert-if-true DirectTrigger $this
     */
    public function isDirect(): bool
    {
        return false;
    }

    /**
     * Third-party code triggers an issue either in your own code or in third-party code.
     *
     * @phpstan-assert-if-true IndirectTrigger $this
     */
    public function isIndirect(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UnknownTrigger $this
     */
    public function isUnknown(): bool
    {
        return false;
    }

    abstract public function asString(): string;
}
