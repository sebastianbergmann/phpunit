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

use ArrayIterator;
use ArrayObject;
use EmptyIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsEmpty::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsEmptyTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                [],
            ],

            [
                true,
                '',
                new EmptyIterator,
            ],

            [
                true,
                '',
                new ArrayObject,
            ],

            [
                true,
                '',
                new ArrayIterator([]),
            ],

            [
                false,
                'Failed asserting that an array is empty.',
                [0],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, mixed $actual): void
    {
        $constraint = new IsEmpty;

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
        $this->assertSame('is empty', (new IsEmpty)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new IsEmpty);
    }
}
