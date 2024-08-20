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

#[CoversMethod(Assert::class, 'assertNotEqualsCanonicalizing')]
#[TestDox('assertNotEqualsCanonicalizing()')]
#[Small]
final class assertNotEqualsCanonicalizingTest extends TestCase
{
    #[DataProviderExternal(assertEqualsCanonicalizingTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual): void
    {
        $this->assertNotEqualsCanonicalizing($expected, $actual);
    }

    #[DataProviderExternal(assertEqualsCanonicalizingTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEqualsCanonicalizing($expected, $actual);
    }
}
