<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\OutsideOfTests;

final class Cases
{
    /**
     * @return list<array{0: string}>
     */
    public static function all(): array
    {
        return [['a'], ['b']];
    }
}
