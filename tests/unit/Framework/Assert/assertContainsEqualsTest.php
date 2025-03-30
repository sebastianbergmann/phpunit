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

#[CoversMethod(Assert::class, 'assertContainsEquals')]
#[TestDox('assertContainsEquals()')]
#[Small]
final class assertContainsEqualsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: iterable}>
     */
    public static function successProvider(): array
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->foo = 'bar';

        return [
            [0, [0]],
            [0, ['0']],
            [0, [0.0]],
            [0, [false]],
            [0, [null]],
            ['string', ['string']],
            [['string'], [['string']]],
            [$a, [$b]],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: iterable}>
     */
    public static function failureProvider(): array
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->foo = 'baz';

        return [
            [1, [0]],
            [1, [0.0]],
            [1, [false]],
            [1, [null]],
            ['string', ['another-string']],
            [['string'], [['another-string']]],
            [$a, [$b]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $needle, iterable $haystack): void
    {
        $this->assertContainsEquals($needle, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $needle, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsEquals($needle, $haystack);
    }
}
