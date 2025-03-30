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

use function fclose;
use function fopen;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\NativeType;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IsType::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsTypeTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                NativeType::Numeric,
                0,
            ],

            [
                true,
                '',
                NativeType::Int,
                0,
            ],

            [
                true,
                '',
                NativeType::Float,
                0.0,
            ],

            [
                true,
                '',
                NativeType::String,
                'string',
            ],

            [
                true,
                '',
                NativeType::Bool,
                false,
            ],

            [
                true,
                '',
                NativeType::Null,
                null,
            ],

            [
                true,
                '',
                NativeType::Array,
                [],
            ],

            [
                true,
                '',
                NativeType::Object,
                new stdClass,
            ],

            [
                true,
                '',
                NativeType::Resource,
                fopen(__FILE__, 'r'),
            ],

            [
                true,
                '',
                NativeType::ClosedResource,
                self::closedResource(),
            ],

            [
                true,
                '',
                NativeType::Scalar,
                0,
            ],

            [
                true,
                '',
                NativeType::Callable,
                static fn () => true,
            ],

            [
                true,
                '',
                NativeType::Iterable,
                [],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, NativeType $expected, mixed $actual): void
    {
        $constraint = new IsType($expected);

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
        $this->assertSame('is of type array', (new IsType(NativeType::Array))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new IsType(NativeType::Array));
    }

    private static function closedResource()
    {
        $resource = fopen(__FILE__, 'r');

        fclose($resource);

        return $resource;
    }
}
