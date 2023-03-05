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

use ArrayObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayHasKey::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class ArrayHasKeyTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                0,
                [0 => 'value'],
            ],

            [
                true,
                '',
                'key',
                ['key' => 'value'],
            ],

            [
                true,
                '',
                'key',
                new ArrayObject(['key' => 'value']),
            ],

            [
                false,
                'Failed asserting that an array has the key 1.',
                1,
                [0 => 'value'],
            ],

            [
                false,
                'Failed asserting that an array has the key \'another-key\'.',
                'another-key',
                ['key' => 'value'],
            ],

            [
                false,
                'Failed asserting that an array has the key \'another-key\'.',
                'another-key',
                new ArrayObject(['key' => 'value']),
            ],

            [
                false,
                'Failed asserting that an array has the key \'key\'.',
                'key',
                null,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, int|string $expected, mixed $actual): void
    {
        $constraint = new ArrayHasKey($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('has the key 0', (new ArrayHasKey(0))->toString());
        $this->assertSame('has the key \'key\'', (new ArrayHasKey('key'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new ArrayHasKey(0)));
    }
}
