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
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class UnknownTrigger extends IssueTrigger
{
    /**
     * @psalm-assert-if-true UnknownTrigger $this
     */
    public function isUnknown(): true
    {
        return true;
    }

    public function asString(): string
    {
        return 'unknown if issue was triggered in first-party code or third-party code';
    }
}
