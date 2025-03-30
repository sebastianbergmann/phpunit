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

#[CoversMethod(Assert::class, 'assertIsNotScalar')]
#[TestDox('assertIsNotScalar()')]
#[Small]
final class assertIsNotScalarTest extends TestCase
{
    #[DataProviderExternal(assertIsScalarTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertIsNotScalar($actual);
    }

    #[DataProviderExternal(assertIsScalarTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsNotScalar($actual);
    }
}
