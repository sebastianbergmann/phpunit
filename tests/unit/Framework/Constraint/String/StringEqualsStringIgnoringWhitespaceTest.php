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

#[CoversClass(StringEqualsStringIgnoringWhitespace::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class StringEqualsStringIgnoringWhitespaceTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                'hello world',
                "hello\tworld",
            ],

            [
                true,
                '',
                'hello world',
                "hello\nworld",
            ],

            [
                true,
                '',
                'hello world',
                'hello  world',
            ],

            [
                true,
                '',
                'hello world',
                "hello\xC2\xA0world",
            ],

            [
                true,
                '',
                'hello world',
                "hello\xE2\x80\xAFworld",
            ],

            [
                true,
                '',
                'hello world',
                "  hello \t world  ",
            ],

            [
                false,
                'Failed asserting that \'hello world\' is equal to "goodbye world" ignoring whitespace.',
                'goodbye world',
                'hello world',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, string $actual): void
    {
        $constraint = new StringEqualsStringIgnoringWhitespace($expected);

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
        $this->assertSame('is equal to "hello world" ignoring whitespace', new StringEqualsStringIgnoringWhitespace('hello world')->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new StringEqualsStringIgnoringWhitespace('hello world'));
    }
}
