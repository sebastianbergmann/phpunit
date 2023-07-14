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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\UnknownTypeException;
use stdClass;

#[CoversClass(IsType::class)]
#[CoversClass(Constraint::class)]
#[CoversClass(UnknownTypeException::class)]
#[Small]
final class IsTypeTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                'numeric',
                0,
            ],

            [
                true,
                '',
                'int',
                0,
            ],

            [
                true,
                '',
                'integer',
                0,
            ],

            [
                true,
                '',
                'double',
                0.0,
            ],

            [
                true,
                '',
                'float',
                0.0,
            ],

            [
                true,
                '',
                'real',
                0.0,
            ],

            [
                true,
                '',
                'string',
                'string',
            ],

            [
                true,
                '',
                'boolean',
                false,
            ],

            [
                true,
                '',
                'bool',
                false,
            ],

            [
                true,
                '',
                'null',
                null,
            ],

            [
                true,
                '',
                'array',
                [],
            ],

            [
                true,
                '',
                'object',
                new stdClass,
            ],

            [
                true,
                '',
                'resource',
                fopen(__FILE__, 'r'),
            ],

            [
                true,
                '',
                'resource (closed)',
                self::closedResource(),
            ],

            [
                true,
                '',
                'scalar',
                0,
            ],

            [
                true,
                '',
                'callable',
                static fn () => true,
            ],

            [
                true,
                '',
                'iterable',
                [],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, mixed $actual): void
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
        $this->assertSame('is of type array', (new IsType('array'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new IsType('array'));
    }

    public function testRejectsUnknownTypes(): void
    {
        $this->expectException(UnknownTypeException::class);

        new IsType('does-not-exist');
    }

    private static function closedResource()
    {
        $resource = fopen(__FILE__, 'r');

        fclose($resource);

        return $resource;
    }
}
