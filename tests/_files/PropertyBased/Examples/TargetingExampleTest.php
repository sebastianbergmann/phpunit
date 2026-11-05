<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Examples;

use const INF;
use const NAN;
use PHPUnit\Framework\TestCase;

final class TargetingExampleTest extends TestCase
{
    public function testTargetsScores(int $number): void
    {
        $this->target('size', $number);
        $this->target('ratio', 0.5);
    }

    public function testTargetsAScoreWithAnEmptyLabel(int $number): void
    {
        $this->target('', $number);
    }

    public function testTargetsNan(int $number): void
    {
        $this->target('ratio', NAN);
    }

    public function testTargetsInfinity(int $number): void
    {
        $this->target('ratio', INF);
    }

    public function testTargetsAScoreWithTheSameLabelTwice(int $number): void
    {
        $this->target('size', $number);
        $this->target('size', $number + 1);
    }

    public function testUsesTargetOutsideOfAProperty(): void
    {
        $this->target('size', 1);
    }
}
