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

use ArrayAccess;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertArrayNotHasKey')]
#[TestDox('assertArrayNotHasKey()')]
#[Small]
final class assertArrayNotHasKeyTest extends TestCase
{
    #[DataProviderExternal(assertArrayHasKeyTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(int|string $key, array|ArrayAccess $array): void
    {
        $this->assertArrayNotHasKey($key, $array);
    }

    #[DataProviderExternal(assertArrayHasKeyTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(int|string $key, array|ArrayAccess $array): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey($key, $array);
    }
}
