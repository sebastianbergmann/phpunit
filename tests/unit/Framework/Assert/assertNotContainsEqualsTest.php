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

#[CoversMethod(Assert::class, 'assertNotContainsEquals')]
#[TestDox('assertNotContainsEquals()')]
#[Small]
final class assertNotContainsEqualsTest extends TestCase
{
    #[DataProviderExternal(assertContainsEqualsTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $needle, iterable $haystack): void
    {
        $this->assertNotContainsEquals($needle, $haystack);
    }

    #[DataProviderExternal(assertContainsEqualsTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $needle, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsEquals($needle, $haystack);
    }
}
