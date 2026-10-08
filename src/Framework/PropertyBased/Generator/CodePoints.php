<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function assert;
use function chr;
use function ord;
use function strlen;
use function strpos;

/**
 * Orders the Unicode scalar values, all code points except the surrogates,
 * so that simpler characters have smaller indices: first the printable ASCII
 * characters, starting with the lowercase letters, then the ASCII control
 * characters, and then all other code points in ascending order.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CodePoints
{
    public const int LARGEST_INDEX         = 1112063;
    private const string ASCII             = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 !\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F";
    private const int FIRST_SURROGATE      = 0xD800;
    private const int LAST_SURROGATE       = 0xDFFF;
    private const int NUMBER_OF_SURROGATES = self::LAST_SURROGATE - self::FIRST_SURROGATE + 1;

    /**
     * @param int<0, self::LARGEST_INDEX> $index
     *
     * @return int<0, 1114111>
     */
    public static function codePoint(int $index): int
    {
        if ($index < strlen(self::ASCII)) {
            $codePoint = ord(self::ASCII[$index]);
        } else {
            $codePoint = $index;

            if ($codePoint >= self::FIRST_SURROGATE) {
                $codePoint += self::NUMBER_OF_SURROGATES;
            }
        }

        return $codePoint;
    }

    /**
     * @param int<0, 1114111> $codePoint a code point that is not a surrogate
     *
     * @return int<0, self::LARGEST_INDEX>
     */
    public static function index(int $codePoint): int
    {
        if ($codePoint < 0x80) {
            $index = strpos(self::ASCII, chr($codePoint));

            assert($index !== false && $index < 0x80);

            return $index;
        }

        if ($codePoint > self::LAST_SURROGATE) {
            $codePoint -= self::NUMBER_OF_SURROGATES;
        }

        assert($codePoint <= self::LARGEST_INDEX);

        return $codePoint;
    }
}
