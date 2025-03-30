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

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\UnknownClassOrInterfaceException;
use stdClass;
use Throwable;

#[CoversClass(IsInstanceOf::class)]
#[CoversClass(Constraint::class)]
#[CoversClass(UnknownClassOrInterfaceException::class)]
#[Small]
final class IsInstanceOfTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                stdClass::class,
                new stdClass,
            ],

            [
                false,
                'Failed asserting that an instance of anonymous class created at',
                stdClass::class,
                new class
                {},
            ],

            [
                false,
                'Failed asserting that an instance of class Exception is an instance of class stdClass.',
                stdClass::class,
                new Exception,
            ],

            [
                false,
                'Failed asserting that an instance of class stdClass is an instance of interface Throwable.',
                Throwable::class,
                new stdClass,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, mixed $actual): void
    {
        $constraint = new IsInstanceOf($expected);

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
        $this->assertSame('is an instance of class stdClass', (new IsInstanceOf(stdClass::class))->toString());
        $this->assertSame('is an instance of interface Throwable', (new IsInstanceOf(Throwable::class))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new IsInstanceOf(stdClass::class));
    }

    public function testRejectsUnknownTypes(): void
    {
        $this->expectException(UnknownClassOrInterfaceException::class);

        new IsInstanceOf('Does\Not\Exist');
    }
}
