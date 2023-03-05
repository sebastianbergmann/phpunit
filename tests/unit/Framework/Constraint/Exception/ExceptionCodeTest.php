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

#[CoversClass(ExceptionCode::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class ExceptionCodeTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                1234,
                1234,
            ],

            [
                false,
                'Failed asserting that 4567 is equal to expected exception code 1234.',
                1234,
                4567,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, int $expected, mixed $actual): void
    {
        $constraint = new ExceptionCode($expected);

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
        $this->assertSame('exception code is 1234', (new ExceptionCode(1234))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new ExceptionCode(1234)));
    }
}
