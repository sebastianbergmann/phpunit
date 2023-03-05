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

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Exception::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class ExceptionTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                RuntimeException::class,
                new RuntimeException,
            ],

            [
                false,
                'Failed asserting that exception of type "RuntimeException" is thrown.',
                RuntimeException::class,
                null,
            ],

            [
                false,
                'Failed asserting that exception of type "InvalidArgumentException" matches expected exception "RuntimeException". Message was: "message" at',
                RuntimeException::class,
                new InvalidArgumentException('message'),
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, mixed $actual): void
    {
        $constraint = new Exception($expected);

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
        $this->assertSame('exception of type "Exception"', (new Exception(\Exception::class))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new Exception(\Exception::class)));
    }
}
