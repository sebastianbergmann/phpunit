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

#[CoversMethod(Assert::class, 'assertContainsNotOnlyInstancesOf')]
#[TestDox('assertContainsNotOnlyInstancesOf()')]
#[Small]
final class assertContainsNotOnlyInstancesOfTest extends TestCase
{
    #[DataProviderExternal(assertContainsOnlyInstancesOfTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $type, iterable $haystack): void
    {
        $this->assertContainsNotOnlyInstancesOf($type, $haystack);
    }

    #[DataProviderExternal(assertContainsOnlyInstancesOfTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $type, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsNotOnlyInstancesOf($type, $haystack);
    }
}
