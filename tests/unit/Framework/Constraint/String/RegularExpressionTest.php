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
use PHPUnit\Framework\TestCase;

#[CoversClass(RegularExpression::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class RegularExpressionTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                '/.*/',
                'string',
            ],

            [
                false,
                'Failed asserting that \'string\' matches PCRE pattern "/[0-9]/".',
                '/[0-9]/',
                'string',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, string $actual): void
    {
        $constraint = new RegularExpression($expected);

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
        $this->assertSame('matches PCRE pattern "/.*/"', (new RegularExpression('/.*/'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new RegularExpression('/.*/')));
    }
}
