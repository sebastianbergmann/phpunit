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

#[CoversMethod(Assert::class, 'assertJsonStringNotEqualsJsonString')]
#[TestDox('assertJsonStringNotEqualsJsonString()')]
#[Small]
final class assertJsonStringNotEqualsJsonStringTest extends TestCase
{
    #[DataProviderExternal(assertJsonStringEqualsJsonStringTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expectedJson, string $actualJson): void
    {
        $this->assertJsonStringNotEqualsJsonString($expectedJson, $actualJson);
    }

    #[DataProviderExternal(assertJsonStringEqualsJsonStringTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expectedJson, string $actualJson): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringNotEqualsJsonString($expectedJson, $actualJson);
    }
}
