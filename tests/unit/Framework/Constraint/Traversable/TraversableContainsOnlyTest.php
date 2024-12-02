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
use PHPUnit\Framework\NativeType;
use PHPUnit\Framework\TestCase;

#[CoversClass(TraversableContainsOnly::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class TraversableContainsOnlyTest extends TestCase
{
    public static function provider(): array
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

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, NativeType $expected, mixed $actual): void
    {
        $constraint = TraversableContainsOnly::forNativeType($expected);

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
        $this->assertSame('contains only values of type "int"', TraversableContainsOnly::forNativeType(NativeType::Int)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, TraversableContainsOnly::forNativeType(NativeType::Int));
    }
}
