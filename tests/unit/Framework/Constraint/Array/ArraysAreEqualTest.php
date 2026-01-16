<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArraysAreEqual::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class ArraysAreEqualTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'empty arrays, keys and order matter' => [
                true,
                '',
                [],
                [],
                true,
                true,
            ],
            'identical arrays, keys and order matter' => [
                true,
                '',
                ['a' => 1, 'b' => 2],
                ['a' => 1, 'b' => 2],
                true,
                true,
            ],
            'equal arrays with type coercion, keys and order matter' => [
                true,
                '',
                ['a' => 1, 'b' => '2'],
                ['a' => '1', 'b' => 2],
                true,
                true,
            ],
            'numeric indexed arrays, keys and order matter' => [
                true,
                '',
                [1, 2, 3],
                [1, 2, 3],
                true,
                true,
            ],
            'same key-value pairs different order, keys matter, order does not matter' => [
                true,
                '',
                ['a' => 1, 'b' => 2],
                ['b' => 2, 'a' => 1],
                true,
                false,
            ],
            'empty arrays, keys matter, order does not matter' => [
                true,
                '',
                [],
                [],
                true,
                false,
            ],
            'same values different keys, keys do not matter, order matters' => [
                true,
                '',
                ['a' => 1, 'b' => 2],
                ['x' => 1, 'y' => 2],
                false,
                true,
            ],
            'numeric arrays same values, keys do not matter, order matters' => [
                true,
                '',
                [1, 2, 3],
                [1, 2, 3],
                false,
                true,
            ],
            'different order and keys, neither keys nor order matter' => [
                true,
                '',
                ['a' => 1, 'b' => 2, 'c' => 3],
                ['z' => 3, 'y' => 1, 'x' => 2],
                false,
                false,
            ],
            'empty arrays, neither keys nor order matter' => [
                true,
                '',
                [],
                [],
                false,
                false,
            ],
            'different values, keys and order matter' => [
                false,
                'Failed asserting that two arrays are equal',
                ['a' => 1, 'b' => 2],
                ['a' => 1, 'b' => 3],
                true,
                true,
            ],
            'different number of elements' => [
                false,
                'Failed asserting that two arrays are equal',
                [1, 2, 3],
                [1, 2],
                true,
                true,
            ],
            'different order when order matters' => [
                false,
                'Failed asserting that two arrays are equal',
                [1, 2, 3],
                [3, 2, 1],
                true,
                true,
            ],
            'different keys when keys matter' => [
                false,
                'Failed asserting that two arrays are equal while ignoring order',
                ['a' => 1, 'b' => 2],
                ['x' => 1, 'y' => 2],
                true,
                false,
            ],
            'different order when only order matters' => [
                false,
                'Failed asserting that two arrays are equal while ignoring keys',
                [1, 2, 3],
                [3, 2, 1],
                false,
                true,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, array $expected, mixed $actual, bool $keysMatter, bool $orderMatters): void
    {
        $constraint = new ArraysAreEqual($expected, $keysMatter, $orderMatters);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testRejectActualThatIsNotAnArray(): void
    {
        $constraint = new ArraysAreEqual([], false, false);

        $this->assertFalse($constraint->evaluate('string', returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('two arrays are equal while ignoring keys and order', new ArraysAreEqual([], false, false)->toString());
        $this->assertSame('two arrays are equal while ignoring order', new ArraysAreEqual([], true, false)->toString());
        $this->assertSame('two arrays are equal while ignoring keys', new ArraysAreEqual([], false, true)->toString());
        $this->assertSame('two arrays are equal', new ArraysAreEqual([], true, true)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new ArraysAreEqual([], false, false));
    }
}
