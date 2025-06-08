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
use stdClass;

#[CoversMethod(Assert::class, 'assertContainsOnlyInstancesOf')]
#[TestDox('assertContainsOnlyInstancesOf()')]
#[Small]
final class assertContainsOnlyInstancesOfTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: class-string, 1: iterable}>
     */
    public static function successProvider(): array
    {
        return [
            [stdClass::class, [new stdClass]],
        ];
    }

    /**
     * @return non-empty-list<array{0: class-string, 1: iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [stdClass::class, [null]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $type, iterable $haystack): void
    {
        $this->assertContainsOnlyInstancesOf($type, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $type, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyInstancesOf($type, $haystack);
    }
}
