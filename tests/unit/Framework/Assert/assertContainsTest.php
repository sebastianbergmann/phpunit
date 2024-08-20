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

#[CoversMethod(Assert::class, 'assertContains')]
#[TestDox('assertContains()')]
#[Small]
final class assertContainsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: iterable}>
     */
    public static function successProvider(): array
    {
        $a = new stdClass;

        return [
            [0, [0]],
            [0.0, [0.0]],
            [false, [false]],
            [null, [null]],
            ['string', ['string']],
            [['string'], [['string']]],
            [$a, [$a]],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [0, ['0']],
            [0, [0.0]],
            [0, [false]],
            [0, [null]],
            [new stdClass, [new stdClass]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $needle, iterable $haystack): void
    {
        $this->assertContains($needle, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $needle, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContains($needle, $haystack);
    }
}
