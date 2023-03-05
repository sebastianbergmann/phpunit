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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(LessThan::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class LessThanTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                1,
                0,
            ],

            [
                false,
                'Failed asserting that 1 is less than 0.',
                0,
                1,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, mixed $expected, mixed $actual): void
    {
        $constraint = new LessThan($expected);

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
        $this->assertSame('is less than 0', (new LessThan(0))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new LessThan(1)));
    }
}
