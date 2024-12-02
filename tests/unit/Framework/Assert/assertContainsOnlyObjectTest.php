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

#[CoversMethod(Assert::class, 'assertContainsOnlyObject')]
#[TestDox('assertContainsOnlyObject()')]
#[Small]
final class assertContainsOnlyObjectTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: iterable}>
     */
    public static function successProvider(): array
    {
        return [
            [[new stdClass]],
        ];
    }

    /**
     * @return non-empty-list<array{0: iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [[null]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(iterable $haystack): void
    {
        $this->assertContainsOnlyObject($haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyObject($haystack);
    }
}
