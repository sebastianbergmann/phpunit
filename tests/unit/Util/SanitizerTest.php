<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function ini_set;
use function is_string;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sanitizer::class)]
#[Small]
final class SanitizerTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: string, 1: string}>
     */
    public static function controlCharacterProvider(): array
    {
        return [
            'U+0000 NULL'                               => ["a\0b", 'a\u{0000}b'],
            'U+0007 BELL'                               => ["a\x07b", 'a\u{0007}b'],
            'U+0008 BACKSPACE'                          => ["a\x08b", 'a\u{0008}b'],
            'U+000B VERTICAL TAB'                       => ["a\x0Bb", 'a\u{000B}b'],
            'U+000C FORM FEED'                          => ["a\x0Cb", 'a\u{000C}b'],
            'U+001B ESCAPE'                             => ["a\x1Bb", 'a\u{001B}b'],
            'U+007F DELETE'                             => ["a\x7Fb", 'a\u{007F}b'],
            'U+0085 NEXT LINE'                          => ["a\u{0085}b", 'a\u{0085}b'],
            'U+009B CONTROL SEQUENCE INTRODUCER'        => ["a\u{009B}31mb", 'a\u{009B}31mb'],
            'U+202A LEFT-TO-RIGHT EMBEDDING'            => ["a\u{202A}b", 'a\u{202A}b'],
            'U+202B RIGHT-TO-LEFT EMBEDDING'            => ["a\u{202B}b", 'a\u{202B}b'],
            'U+202C POP DIRECTIONAL FORMATTING'         => ["a\u{202C}b", 'a\u{202C}b'],
            'U+202D LEFT-TO-RIGHT OVERRIDE'             => ["a\u{202D}b", 'a\u{202D}b'],
            'U+202E RIGHT-TO-LEFT OVERRIDE'             => ["a\u{202E}b", 'a\u{202E}b'],
            'U+2066 LEFT-TO-RIGHT ISOLATE'              => ["a\u{2066}b", 'a\u{2066}b'],
            'U+2067 RIGHT-TO-LEFT ISOLATE'              => ["a\u{2067}b", 'a\u{2067}b'],
            'U+2068 FIRST STRONG ISOLATE'               => ["a\u{2068}b", 'a\u{2068}b'],
            'U+2069 POP DIRECTIONAL ISOLATE'            => ["a\u{2069}b", 'a\u{2069}b'],
            'lone carriage return'                      => ["a\rb", 'a\u{000D}b'],
            'carriage return at end of string'          => ["a\r", 'a\u{000D}'],
            'ANSI SGR sequence'                         => ["\x1B[31mred\x1B[0m", '\u{001B}[31mred\u{001B}[0m'],
            'ANSI erase line and carriage return'       => ["FAIL\x1B[2K\rOK", 'FAIL\u{001B}[2K\u{000D}OK'],
            'ANSI OSC hyperlink'                        => ["\x1B]8;;https://example.com\x1B\\link\x1B]8;;\x1B\\", '\u{001B}]8;;https://example.com\u{001B}\link\u{001B}]8;;\u{001B}\\'],
            'ANSI OSC terminated by BEL'                => ["\x1B]0;title\x07", '\u{001B}]0;title\u{0007}'],
            'multiple bidirectional control characters' => [
                "http://example.com/\u{202E}/foo/\u{202D}/bar",
                'http://example.com/\u{202E}/foo/\u{202D}/bar',
            ],
            'empty string'                          => ['', ''],
            'plain ASCII'                           => ['hello world', 'hello world'],
            'line feed'                             => ["a\nb", "a\nb"],
            'horizontal tab'                        => ["a\tb", "a\tb"],
            'carriage return followed by line feed' => ["a\r\nb", "a\r\nb"],
            'non-bidirectional Unicode'             => ['Кириллица and 中文', 'Кириллица and 中文'],
            'non-control character in same range'   => ["a\u{2065}b", "a\u{2065}b"],
            'U+00A1 INVERTED EXCLAMATION MARK'      => ["a\u{00A1}b", "a\u{00A1}b"],
            'invalid UTF-8'                         => ["a\xFF\xFEb", "a\xFF\xFEb"],
        ];
    }

    #[DataProvider('controlCharacterProvider')]
    public function testSanitizesControlCharacters(string $input, string $expected): void
    {
        $this->assertSame($expected, Sanitizer::sanitizeControlCharacters($input));
    }

    /**
     * The regular expression used by the sanitizer does not backtrack when it is
     * executed by the PCRE JIT compiler. Therefore, the JIT compiler has to be
     * disabled before the regular expression is compiled and cached so that
     * lowering the backtrack limit makes preg_replace_callback() fail.
     *
     * The backtrack limit affects every regular expression that is evaluated
     * afterwards, including the ones used by PHPUnit itself. It therefore has to
     * be restored before this test method returns.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testReturnsValueUnchangedWhenSanitizationFails(): void
    {
        ini_set('pcre.jit', '0');

        $backtrackLimit = ini_set('pcre.backtrack_limit', '1');

        $sanitized = Sanitizer::sanitizeControlCharacters("a\u{202A}b");

        if (is_string($backtrackLimit)) {
            ini_set('pcre.backtrack_limit', $backtrackLimit);
        }

        $this->assertSame("a\u{202A}b", $sanitized);
    }
}
