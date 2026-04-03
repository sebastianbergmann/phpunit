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
use PHPUnit\Framework\TestCase;

#[CoversClass(ExceptionMessageIs::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class ExceptionMessageIsTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                'expected-message',
                'expected-message',
            ],

            [
                true,
                '',
                '',
                '',
            ],

            [
                false,
                "Failed asserting that exception message is empty but is 'actual-message'.",
                '',
                'actual-message',
            ],

            [
                false,
                "Failed asserting that exception message 'actual-message' is 'expected-message'.",
                'expected-message',
                'actual-message',
            ],

            [
                false,
                "Failed asserting that exception message 'expected-message and more' is 'expected-message'.",
                'expected-message',
                'expected-message and more',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, mixed $actual): void
    {
        $constraint = new ExceptionMessageIs($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame("exception message is 'message'", new ExceptionMessageIs('message')->toString());
        $this->assertSame('exception message is empty', new ExceptionMessageIs('')->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new ExceptionMessageIs('message')));
    }
}
