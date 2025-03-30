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

#[CoversMethod(Assert::class, 'assertObjectNotEquals')]
#[TestDox('assertObjectNotEquals()')]
#[Small]
final class assertObjectNotEqualsTest extends TestCase
{
    #[DataProviderExternal(assertObjectEqualsTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(object $expected, object $actual, string $method): void
    {
        $this->assertObjectNotEquals($expected, $actual, $method);
    }

    #[DataProviderExternal(assertObjectEqualsTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(object $expected, object $actual, string $method): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotEquals($expected, $actual, $method);
    }
}
