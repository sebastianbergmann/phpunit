<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

final class Calculator
{
    public function add(int $a, int $b): int
    {
        return (new Rounder)->round($a + $b);
    }
}
