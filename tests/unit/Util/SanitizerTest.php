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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sanitizer::class)]
#[Small]
final class SanitizerTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: string, 1: string}>
     */
    public static function bidirectionalControlCharacterProvider(): array
    {
        return [
            'U+202A LEFT-TO-RIGHT EMBEDDING'            => ["a\u{202A}b", 'a\u{202A}b'],
            'U+202B RIGHT-TO-LEFT EMBEDDING'            => ["a\u{202B}b", 'a\u{202B}b'],
            'U+202C POP DIRECTIONAL FORMATTING'         => ["a\u{202C}b", 'a\u{202C}b'],
            'U+202D LEFT-TO-RIGHT OVERRIDE'             => ["a\u{202D}b", 'a\u{202D}b'],
            'U+202E RIGHT-TO-LEFT OVERRIDE'             => ["a\u{202E}b", 'a\u{202E}b'],
            'U+2066 LEFT-TO-RIGHT ISOLATE'              => ["a\u{2066}b", 'a\u{2066}b'],
            'U+2067 RIGHT-TO-LEFT ISOLATE'              => ["a\u{2067}b", 'a\u{2067}b'],
            'U+2068 FIRST STRONG ISOLATE'               => ["a\u{2068}b", 'a\u{2068}b'],
            'U+2069 POP DIRECTIONAL ISOLATE'            => ["a\u{2069}b", 'a\u{2069}b'],
            'multiple bidirectional control characters' => [
                "http://example.com/\u{202E}/foo/\u{202D}/bar",
                'http://example.com/\u{202E}/foo/\u{202D}/bar',
            ],
            'empty string'                        => ['', ''],
            'plain ASCII'                         => ['hello world', 'hello world'],
            'non-bidirectional Unicode'           => ['Кириллица and 中文', 'Кириллица and 中文'],
            'non-control character in same range' => ["a\u{2065}b", "a\u{2065}b"],
        ];
    }

    #[DataProvider('bidirectionalControlCharacterProvider')]
    public function testSanitizesBidirectionalControlCharacters(string $input, string $expected): void
    {
        $this->assertSame($expected, Sanitizer::sanitizeBidirectionalControlCharacters($input));
    }
}
