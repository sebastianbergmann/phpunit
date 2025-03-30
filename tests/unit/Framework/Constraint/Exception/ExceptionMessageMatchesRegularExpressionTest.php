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
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExceptionMessageMatchesRegularExpression::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class ExceptionMessageMatchesRegularExpressionTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                '/[0-9]/',
                '1234',
            ],

            [
                false,
                'Failed asserting that exception message \'abcd\' matches \'/[0-9]/\'.',
                '/[0-9]/',
                'abcd',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, mixed $actual): void
    {
        $constraint = new ExceptionMessageMatchesRegularExpression($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testRejectsInvalidRegularExpression(): void
    {
        $constraint = new ExceptionMessageMatchesRegularExpression('invalid regular expression');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid expected exception message regular expression given: invalid regular expression');

        $constraint->evaluate('abcd');
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('exception message matches \'/.*/\'', (new ExceptionMessageMatchesRegularExpression('/.*/'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new ExceptionMessageMatchesRegularExpression('/.*/')));
    }
}
