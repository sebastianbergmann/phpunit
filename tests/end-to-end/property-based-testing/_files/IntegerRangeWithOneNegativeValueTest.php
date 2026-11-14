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

use function array_filter;
use function count;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class IntegerRangeWithOneNegativeValueTest extends TestCase
{
    /**
     * @var list<int>
     */
    private static array $limits = [];

    /**
     * @param int<-1, max> $limit
     */
    #[Property(examples: 1000)]
    public function testLimit(int $limit, string $name): void
    {
        self::$limits[] = $limit;

        $this->assertGreaterThanOrEqual(-1, $limit);
    }

    #[Depends('testLimit')]
    public function testTheNegativeValueIsGeneratedForAboutATenthOfTheExamples(): void
    {
        $negative = array_filter(self::$limits, static fn (int $limit): bool => $limit === -1);

        $this->assertCount(1000, self::$limits);
        $this->assertEqualsWithDelta(0.1, count($negative) / count(self::$limits), 0.03);
    }
}
