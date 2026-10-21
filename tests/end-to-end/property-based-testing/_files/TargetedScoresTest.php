<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use function abs;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class TargetedScoresTest extends TestCase
{
    /**
     * @param int<0, 2000000> $value
     */
    #[Property(examples: 200)]
    public function testFindsTheRareValuesThatFalsifyItByTargetingHowCloseValuesComeToThem(int $value): void
    {
        $this->target('closeness', -abs($value - 1_000_050));

        $this->assertFalse($value >= 1_000_000 && $value <= 1_000_100);
    }

    /**
     * @param int<0, 2000000> $value
     */
    #[Property(examples: 200)]
    public function testDoesNotFindTheRareValuesThatFalsifyItWithoutTargetingThem(int $value): void
    {
        $this->assertFalse($value >= 1_000_000 && $value <= 1_000_100);
    }
}
