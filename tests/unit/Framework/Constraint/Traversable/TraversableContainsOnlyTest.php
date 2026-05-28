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
use PHPUnit\Framework\NativeType;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(TraversableContainsOnly::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class TraversableContainsOnlyTest extends TestCase
{
    /**
     * @return non-empty-list<array{bool, string, NativeType, mixed}>
     */
    public static function nativeTypeProvider(): array
    {
        return [
            [
                true,
                '',
                NativeType::Int,
                [0, 1, 2],
            ],

            [
                false,
                <<<'EOT'
Failed asserting that Array &0 [
    0 => 0,
    1 => '1',
    2 => 2,
] contains only values of type "int".
EOT,
                NativeType::Int,
                [0, '1', 2],
            ],
        ];
    }

    /**
     * @return non-empty-list<array{bool, string, string, mixed}>
     */
    public static function classOrInterfaceProvider(): array
    {
        return [
            [
                true,
                '',
                stdClass::class,
                [new stdClass, new stdClass],
            ],

            [
                false,
                <<<'EOT'
Failed asserting that Array &0 [
    0 => null,
] contains only values of type "stdClass".
EOT,
                stdClass::class,
                [null],
            ],
        ];
    }

    #[DataProvider('nativeTypeProvider')]
    public function testCanBeEvaluatedForNativeType(bool $result, string $failureDescription, NativeType $expected, mixed $actual): void
    {
        $constraint = TraversableContainsOnly::forNativeType($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIsOrContains($failureDescription);

        $constraint->evaluate($actual);
    }

    /**
     * @param class-string $expected
     */
    #[DataProvider('classOrInterfaceProvider')]
    public function testCanBeEvaluatedForClassOrInterface(bool $result, string $failureDescription, string $expected, mixed $actual): void
    {
        $constraint = TraversableContainsOnly::forClassOrInterface($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIsOrContains($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsStringForNativeType(): void
    {
        $this->assertSame('contains only values of type "int"', TraversableContainsOnly::forNativeType(NativeType::Int)->toString());
    }

    public function testCanBeRepresentedAsStringForClassOrInterface(): void
    {
        $this->assertSame('contains only values of type "stdClass"', TraversableContainsOnly::forClassOrInterface(stdClass::class)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(TraversableContainsOnly::forNativeType(NativeType::Int));

        $this->assertSame('does not contain only values of type "int"', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageMatches('/does not contain only values of type "int"/');

        $constraint->evaluate([1, 2]);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, TraversableContainsOnly::forNativeType(NativeType::Int));
    }

    public function testFailsForNonIterableActual(): void
    {
        $this->assertFalse(TraversableContainsOnly::forNativeType(NativeType::Int)->evaluate('not iterable', returnResult: true));
    }

    public function testReturnsAffirmativeStringInNonLogicalNotContext(): void
    {
        $this->assertSame(
            'contains only values of type "int"',
            LogicalAnd::fromConstraints(TraversableContainsOnly::forNativeType(NativeType::Int))->toString(),
        );
    }
}
