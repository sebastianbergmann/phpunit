<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\RegularExpression;

use const PREG_SET_ORDER;
use function assert;
use function mb_convert_encoding;
use function mb_ord;
use function mb_substr;
use function pack;
use function preg_match_all;
use function range;

/**
 * The characters that an escape sequence such as \w, or a POSIX class such as
 * [:alpha:], matches in a regular expression with the u modifier.
 *
 * The u modifier makes them match characters by their Unicode properties, so
 * that \w matches letters such as é, and \d the digits of all scripts. Which
 * characters these are depends on the version of Unicode that PCRE was built
 * for, and on the version of PCRE. PCRE is therefore asked for them: it looks
 * for the characters that the escape sequence or the POSIX class matches in
 * all Unicode scalar values.
 *
 * This is only done when a regular expression needs these characters, and
 * once for each escape sequence and each POSIX class, as it takes some tens
 * of milliseconds.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class UnicodeShorthands
{
    private const int LARGEST_CODE_POINT = 0x10FFFF;
    private const int FIRST_SURROGATE    = 0xD800;

    /**
     * The surrogates, which are not characters, are one such block.
     */
    private const int SIZE_OF_BLOCK = 0x800;

    /**
     * @var array<non-empty-string, CharacterSet>
     */
    private static array $characters = [];

    /**
     * @param non-empty-string $shorthand an escape sequence or a POSIX class as it is written in a character class, \w or [:alpha:], for instance
     */
    public static function matchedBy(string $shorthand): CharacterSet
    {
        if (isset(self::$characters[$shorthand])) {
            return self::$characters[$shorthand];
        }

        // the characters of a block are consecutive ones: each run of
        // characters that are matched is a range of characters, and the
        // characters that are not matched are consumed in runs as well, so
        // that the search does not start again at each of them
        $runs   = '/([' . $shorthand . ']++)|[^' . $shorthand . ']++/u';
        $ranges = [];

        for ($block = 0; $block <= self::LARGEST_CODE_POINT; $block += self::SIZE_OF_BLOCK) {
            if ($block === self::FIRST_SURROGATE) {
                continue;
            }

            $characters = mb_convert_encoding(pack('N*', ...range($block, $block + self::SIZE_OF_BLOCK - 1)), 'UTF-8', 'UTF-32BE');

            assert($characters !== false);

            // like where a generated character is checked, nothing is matched
            // when PCRE gives up, because of pcre.backtrack_limit, for instance
            preg_match_all($runs, $characters, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                // a run of characters that are not matched is not captured
                if (!isset($match[1])) {
                    continue;
                }

                $first = mb_ord($match[1], 'UTF-8');
                $last  = mb_ord(mb_substr($match[1], -1, 1, 'UTF-8'), 'UTF-8');

                assert($first !== false && $first >= 0);
                assert($last !== false && $last >= 0);

                $ranges[] = [$first, $last];
            }
        }

        self::$characters[$shorthand] = CharacterSet::fromRanges($ranges, true);

        return self::$characters[$shorthand];
    }
}
