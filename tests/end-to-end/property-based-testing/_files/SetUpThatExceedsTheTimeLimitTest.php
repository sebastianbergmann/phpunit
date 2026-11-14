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

use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[Small]
final class SetUpThatExceedsTheTimeLimitTest extends TestCase
{
    protected function setUp(): void
    {
        usleep(2_000_000);
    }

    /**
     * @param int<0, 50> $value
     */
    #[Property(examples: 10)]
    public function testGeneratedExample(int $value): void
    {
        $this->assertLessThanOrEqual(50, $value);
    }

    /**
     * @param int<0, 50> $value
     */
    #[Property(examples: 10)]
    #[TestWith([25])]
    public function testExplicitExample(int $value): void
    {
        $this->assertLessThanOrEqual(50, $value);
    }
}
