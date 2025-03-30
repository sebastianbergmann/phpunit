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
final class IndirectTrigger extends IssueTrigger
{
    /**
     * Third-party code triggers an issue either in your own code or in third-party code.
     */
    public function isIndirect(): true
    {
        return true;
    }

    public function asString(): string
    {
        return 'issue triggered by third-party code';
    }
}
