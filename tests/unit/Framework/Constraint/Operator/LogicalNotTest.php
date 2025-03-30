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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogicalNot::class)]
#[CoversClass(UnaryOperator::class)]
#[CoversClass(Operator::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class LogicalNotTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                self::logicalNot(self::isTrue()),
                false,
            ],

            [
                false,
                'Failed asserting that true is not true.',
                self::logicalNot(self::isTrue()),
                true,
            ],

            [
                false,
                'Failed asserting that not( true is true or is true ).',
                self::logicalNot(
                    self::logicalOr(
                        self::isTrue(),
                        self::isTrue(),
                    ),
                ),
                true,
            ],
        ];
    }

    public static function negateProvider(): array
    {
        return [
            ['ocean contains water', 'ocean does not contain water'],
            [
                '\'this is water\' contains "water" and contains "is"',
                '\'this is water\' does not contain "water" and does not contain "is"',
            ],
            ['what it contains', 'what it contains'],
            ['life exists in outer space', 'life does not exist in outer space'],
            ['alien exists', 'alien does not exist'],
            ['it coexists', 'it coexists'],
            ['the dog has a bone', 'the dog does not have a bone'],
            ['whatever it has', 'whatever it has'],
            ['apple is red', 'apple is not red'],
            ['yes, it is', 'yes, it is'],
            ['this is clock', 'this is not clock'],
            ['how are you?', 'how are not you?'],
            ['how dare you!', 'how dare you!'],
            ['what they are', 'what they are'],
            ['that matches my preferences', 'that does not match my preferences'],
            ['dinner starts with desert', 'dinner starts not with desert'],
            ['it starts with', 'it starts with'],
            ['dinner ends with desert', 'dinner ends not with desert'],
            ['it ends with', 'it ends with'],
            ['you reference me', 'you don\'t reference me'],
            ['it\'s not not false', 'it\'s not false'],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, LogicalNot $constraint, mixed $actual): void
    {
        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    #[DataProvider('negateProvider')]
    public function testCanNegateStatement(string $input, string $expected): void
    {
        $this->assertSame($expected, LogicalNot::negate($input));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $constraint = $this->logicalNot(
            $this->logicalOr(
                $this->isTrue(),
                $this->isFalse(),
            ),
        );

        $this->assertSame('not( is true or is false )', $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $constraint = $this->logicalNot(
            $this->logicalOr(
                $this->isTrue(),
                $this->isFalse(),
            ),
        );

        $this->assertCount(2, $constraint);
    }

    #[TestDox('LogicalNot(IsEqual(\'test contains something\')) is handled correctly')]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/5516')]
    public function testForNotEqualsWithStringThatContainsContains(): void
    {
        $constraint = new LogicalNot(new IsEqual('test contains something'));

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Failed asserting that 'test contains something' is not equal to 'test contains something'.");

        Assert::assertThat('test contains something', $constraint);
    }
}
