<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertNotEqualsWithDelta')]
#[TestDox('assertNotEqualsWithDelta()')]
#[Small]
final class assertNotEqualsWithDeltaTest extends TestCase
{
    #[DataProviderExternal(assertEqualsWithDeltaTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual, float $delta): void
    {
        $this->assertNotEqualsWithDelta($expected, $actual, $delta);
    }

    #[DataProviderExternal(assertEqualsWithDeltaTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual, float $delta): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEqualsWithDelta($expected, $actual, $delta);
    }
}
