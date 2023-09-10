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
    public static function providesEvaluationCases(): array
    {
        return [
            'It finds the needle with default options given' => [
                true,
                '',
                false,
                false,
                'needle',
                'prefix needle suffix',
            ],

            'Empty needles are supported with default options given' => [
                true,
                '',
                false,
                false,
                '',
                'prefix needle suffix',
            ],

            'It finds the needle with default options given but different encodings are used' => [
                true,
                '',
                false,
                false,
                'character', // Example ASCII needle string
                'Example character encoding string', // Example UTF-8 haystack string
            ],

            'It finds the needle given letter casing is ignored and both haystack and needle are in the same case' => [
                true,
                '',
                true,
                false,
                'needle',
                'prefix needle suffix',
            ],

            'It finds the needle given letter casing is ignored and needle is in a different case to haystack' => [
                true,
                '',
                true,
                false,
                'needle',
                'prefix NEEDLE suffix',
            ],

            'It finds the needle given letter casing is ignored and haystack is in a different case to needle' => [
                true,
                '',
                true,
                false,
                'NEEDLE',
                'prefix needle suffix',
            ],

            'Needles containing only line endings are supported given line endings are set up to be ignored' => [
                true,
                '',
                false,
                true,
                "\n",
                "prefix needle\r\n suffix",
            ],

            'It supports the needle and haystack using different line endings given line endings are ignored' => [
                true,
                '',
                false,
                true,
                "needle\r suffix",
                "prefix needle\n suffix",
            ],

            '\r\n line endings will be ignored in the needle given line endings are set up to be ignored' => [
                true,
                '',
                false,
                true,
                "needle\r\n suffix",
                "prefix needle\r suffix",
            ],

            '\r\n line endings will be ignored in the haystack given line endings are set up to be ignored' => [
                true,
                '',
                true,
                true,
                "needle\n",
                "prefix NEEDLE\r\n suffix",
            ],

            'It fails to find the needle given the haystack is null' => [
                false,
                'Failed asserting that null [Encoding detection failed](length: 0) contains "needle" [ASCII](length: 6).',
                false,
                false,
                'needle',
                null,
            ],

            'It fails to find the needle given the haystack does not contain it' => [
                false,
                'Failed asserting that \'prefix ... suffix\' [ASCII](length: 17) contains "needle" [ASCII](length: 6).',
                false,
                false,
                'needle',
                'prefix ... suffix',
            ],

            'Encoding is ignored given letter casing is ignored' => [
                false,
                'Failed asserting that \'Example UTF-8 encoded string £$\' [Encoding ignored](length: 32) contains "example ascii encoded string that is not a needle of the utf-8 one" [Encoding ignored](length: 66).',
                true,
                false,
                'Example ASCII encoded string that is not a needle of the UTF-8 one',
                'Example UTF-8 encoded string £$',
            ],

            'The length and detecting encoding is included in the failure message' => [
                false,
                'Failed asserting that \'Example character encoding\' [UTF-8](length: 30) contains "Example character encoding" [ASCII](length: 26).',
                false,
                false,
                /**
                 * Below is an ASCII string using a 'blank space' character (code 32 in https://smartwebworker.com/ascii-codes)
                 * between each word.
                 */
                'Example character encoding',
                /**
                 * Below is a UTF-8 string using a 'thin-space' character (https://www.compart.com/en/unicode/U+2009)
                 * between each word instead of usual 'space' character (https://www.compart.com/en/unicode/U+0020).
                 */
                'Example character encoding',
            ],

            'Both the needle and haystack length in the failure message partly account for \r line endings given line endings are ignored' => [
                false,
                "Failed asserting that 'Some haystack with\\r\n line\\n\n endings \\n\\r\n' [ASCII](length: 36) contains \"Some needle with\n line\n endings \n\n\" [ASCII](length: 34).",
                false,
                true,
                /**
                 * See StringContains::normalizeLineEndings() to
                 * see how "\r" are mapped to "\n".
                 */
                "Some needle with\r line\n endings \n\r", // 38 characters long
                "Some haystack with\r line\n endings \n\r", // 39 characters long
            ],
        ];
    }

    public static function providesToStringRepresentationCases(): array
    {
        return [
            'It contains the needle\'s string, length, and encoding information' => [
                'contains "needle" [ASCII](length: 6)',
                'needle',
                false,
                false,
            ],

            'It contains the needle\'s string, length, and encoding information when using a non-ASCII encoding' => [
                'contains "example UTF-8 needle £$" [UTF-8](length: 24)',
                'example UTF-8 needle £$',
                false,
                false,
            ],

            'It contains the converted-to-lower-case needle string given letter casing is ignored' => [
                'contains "needle" [Encoding ignored](length: 6)',
                'NEEDLE',
                true,
                false,
            ],

            'It maps out the \r line endings from needle string given line endings are ignored' => [
                'contains "NEEDLE' . "\n" . '" [ASCII](length: 7)',
                "NEEDLE\r\n",
                false,
                true,
            ],
        ];
    }

    #[DataProvider('providesEvaluationCases')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, bool $ignoreCase, bool $ignoreLineEndings, string $needle, mixed $haystack): void
    {
        $constraint = new StringContains($needle, $ignoreCase, $ignoreLineEndings);

        $this->assertSame($result, $constraint->evaluate($haystack, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($haystack);
    }

    #[DataProvider('providesToStringRepresentationCases')]
    public function testCanBeRepresentedAsString(string $expected, string $needle, bool $ignoreCase, bool $ignoreLineEndings): void
    {
        $this->assertSame($expected, (new StringContains($needle, $ignoreCase, $ignoreLineEndings))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new StringContains('needle')));
    }
}
