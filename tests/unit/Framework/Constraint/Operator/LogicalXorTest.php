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

#[CoversClass(LogicalXor::class)]
#[CoversClass(BinaryOperator::class)]
#[CoversClass(Operator::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class LogicalXorTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                'is false xor is true',
                '',
                self::logicalXor(
                    self::isFalse(),
                    self::isTrue(),
                ),
                true,
            ],

            [
                true,
                'is false xor is equal to true',
                '',
                self::logicalXor(
                    self::isFalse(),
                    true,
                ),
                true,
            ],

            [
                true,
                'is of type bool xor is true',
                '',
                self::logicalXor(
                    self::isBool(),
                    self::isTrue(),
                ),
                false,
            ],

            [
                false,
                'is of type bool xor is true',
                'Failed asserting that true is of type bool xor is true.',
                self::logicalXor(
                    self::isBool(),
                    self::isTrue(),
                ),
                true,
            ],

            [
                false,
                'is of type bool and is true xor is of type bool and is true',
                'Failed asserting that true is of type bool and is true xor is of type bool and is true.',
                self::logicalXor(
                    self::logicalAnd(
                        self::isBool(),
                        self::isTrue(),
                    ),
                    self::logicalAnd(
                        self::isBool(),
                        self::isTrue(),
                    ),
                ),
                true,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $constraintAsString, string $failureDescription, LogicalXor $constraint, mixed $actual): void
    {
        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    #[DataProvider('provider')]
    public function testCanBeRepresentedAsString(bool $result, string $constraintAsString, string $failureDescription, LogicalXor $constraint, mixed $actual): void
    {
        $this->assertSame($constraintAsString, $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $constraint = $this->logicalXor(
            $this->isBool(),
            true,
        );

        $this->assertCount(2, $constraint);
    }
}
