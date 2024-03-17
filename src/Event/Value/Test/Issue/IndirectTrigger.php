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
final class IndirectTrigger extends IssueTrigger
{
    /**
     * Your own code triggers an issue in your own code.
     *
     * @psalm-assert-if-true IndirectTrigger $this
     */
    public function isIndirect(): true
    {
        return true;
    }

    public function asString(): string
    {
        return 'third-party code triggered issue in third-party code';
    }
}
