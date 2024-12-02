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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertContainsOnlyNull')]
#[TestDox('assertContainsOnlyNull()')]
#[Small]
final class assertContainsOnlyNullTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: iterable}>
     */
    public static function successProvider(): array
    {
        return [
            [[null]],
        ];
    }

    /**
     * @return non-empty-list<array{0: iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [[true]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(iterable $haystack): void
    {
        $this->assertContainsOnlyNull($haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyNull($haystack);
    }
}
