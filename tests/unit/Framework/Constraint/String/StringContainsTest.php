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

#[CoversClass(StringContains::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class StringContainsTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                false,
                false,
                '',
                'prefix substring suffix',
            ],

            [
                true,
                '',
                false,
                false,
                'substring',
                'prefix substring suffix',
            ],

            [
                true,
                '',
                true,
                false,
                'substring',
                'prefix substring suffix',
            ],

            [
                true,
                '',
                true,
                false,
                'substring',
                'prefix SUBSTRING suffix',
            ],

            [
                true,
                '',
                true,
                false,
                'SUBSTRING',
                'prefix substring suffix',
            ],

            [
                true,
                '',
                false,
                true,
                "substring\n",
                "prefix substring\r\n suffix",
            ],

            [
                true,
                '',
                false,
                true,
                "substring\r suffix",
                "prefix substring\n suffix",
            ],

            [
                true,
                '',
                false,
                true,
                "substring\r\n suffix",
                "prefix substring\r suffix",
            ],

            [
                true,
                '',
                true,
                true,
                "substring\n",
                "prefix SUBSTRING\r\n suffix",
            ],

            [
                false,
                'Failed asserting that null contains "substring".',
                false,
                false,
                'substring',
                null,
            ],
            [
                false,
                'Failed asserting that \'prefix ... suffix\' contains "substring".',
                false,
                false,
                'substring',
                'prefix ... suffix',
            ],
            [
                false,
                'Failed asserting that \'Example character encoding\' contains "Example character encoding".', // These looks identical in console output
                false,
                false,
                /**
                 * Below is an ASCII string using a 'blank space' character (code 32 in https://smartwebworker.com/ascii-codes)
                 * between each word
                 */
                'Example character encoding',
                /**
                 * Below is a UTF-8 string using a 'thin-space' character (https://www.compart.com/en/unicode/U+2009)
                 * between each word instead of usual 'space' character (https://www.compart.com/en/unicode/U+0020)
                 */
                'Example character encoding',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, bool $ignoreCase, bool $ignoreLineEndings, string $expected, mixed $actual): void
    {
        $constraint = new StringContains($expected, $ignoreCase, $ignoreLineEndings);

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
        $this->assertSame('contains "substring"', (new StringContains('substring'))->toString());
        $this->assertSame('contains "substring"', (new StringContains('SUBSTRING', true))->toString());
        $this->assertSame('contains "SUBSTRING' . "\n" . '"', (new StringContains("SUBSTRING\r\n", ignoreLineEndings: true))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new StringContains('substring')));
    }
}
