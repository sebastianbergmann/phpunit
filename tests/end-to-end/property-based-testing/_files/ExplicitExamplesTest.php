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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestWithJson;
use PHPUnit\Framework\TestCase;

final class ExplicitExamplesTest extends TestCase
{
    /**
     * @var list<array{int, int}>
     */
    private static array $arguments = [];

    /**
     * @param int<0, 1000> $a
     * @param int<0, 1000> $b
     */
    #[Property(examples: 3)]
    #[TestWith([-1, -2])]
    #[TestWith(['b' => -4, 'a' => -3], 'named arguments')]
    #[TestWithJson('[-5, -6]')]
    public function testAdditionIsCommutative(int $a, int $b): void
    {
        self::$arguments[] = [$a, $b];

        $this->assertSame($a + $b, $b + $a);
    }

    #[Depends('testAdditionIsCommutative')]
    public function testExplicitExamplesAreCheckedFirstAndDoNotCountTowardsTheNumberOfExamples(): void
    {
        $this->assertCount(6, self::$arguments);
        $this->assertSame([[-1, -2], [-3, -4], [-5, -6]], [self::$arguments[0], self::$arguments[1], self::$arguments[2]]);
    }
}
