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

use function assert;
use function chr;
use function count;
use function max;
use function mb_chr;
use function min;
use function usort;
use PHPUnit\Framework\PropertyBased\Generator\CodePoints;

/**
 * A set of characters: of Unicode scalar values, which are encoded as UTF-8,
 * for a regular expression with the u modifier, and of bytes otherwise.
 *
 * The members of the set are ordered so that simpler characters come first,
 * as for strings that are generated from any characters: the ASCII
 * characters in the order of CodePoints, starting with the lowercase
 * letters, and then all other characters in ascending order.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CharacterSet
{
    private const int LARGEST_BYTE          = 0xFF;
    private const int LARGEST_CODE_POINT    = 0x10FFFF;
    private const int FIRST_SURROGATE       = 0xD800;
    private const int LAST_SURROGATE        = 0xDFFF;
    private const int NUMBER_OF_ASCII_CODES = 0x80;

    /**
     * Sorted ranges that neither overlap nor touch.
     *
     * @var list<array{non-negative-int, non-negative-int}>
     */
    private array $ranges;
    private bool $utf8;

    /**
     * The ASCII characters of the set, in the order of CodePoints.
     *
     * @var list<non-negative-int>
     */
    private array $asciiMembers;

    /**
     * @var non-negative-int
     */
    private int $size;

    /**
     * @param list<array{non-negative-int, non-negative-int}> $ranges ranges of characters from the first to the last one of each, in any order
     * @param bool                                            $utf8   whether the characters are Unicode scalar values rather than bytes
     */
    public static function fromRanges(array $ranges, bool $utf8): self
    {
        usort(
            $ranges,
            static fn (array $a, array $b): int => $a[0] <=> $b[0],
        );

        $merged  = [];
        $current = null;

        foreach ($ranges as [$first, $last]) {
            if ($current !== null && $first <= $current[1] + 1) {
                $current = [$current[0], max($current[1], $last)];

                continue;
            }

            if ($current !== null) {
                $merged[] = $current;
            }

            $current = [$first, $last];
        }

        if ($current !== null) {
            $merged[] = $current;
        }

        return new self($merged, $utf8);
    }

    /**
     * @param list<non-negative-int> $characters
     */
    public static function fromCharacters(array $characters, bool $utf8): self
    {
        $ranges = [];

        foreach ($characters as $character) {
            $ranges[] = [$character, $character];
        }

        return self::fromRanges($ranges, $utf8);
    }

    /**
     * All characters: the Unicode scalar values, that is, all code points
     * except the surrogates, or all bytes.
     */
    public static function all(bool $utf8): self
    {
        if ($utf8) {
            return new self([[0, self::FIRST_SURROGATE - 1], [self::LAST_SURROGATE + 1, self::LARGEST_CODE_POINT]], true);
        }

        return new self([[0, self::LARGEST_BYTE]], false);
    }

    /**
     * The ASCII characters.
     */
    public static function ascii(bool $utf8): self
    {
        return new self([[0, self::NUMBER_OF_ASCII_CODES - 1]], $utf8);
    }

    /**
     * @param list<array{non-negative-int, non-negative-int}> $ranges sorted ranges that neither overlap nor touch
     */
    private function __construct(array $ranges, bool $utf8)
    {
        $this->ranges = $ranges;
        $this->utf8   = $utf8;

        $asciiMembers = [];

        for ($index = 0; $index < self::NUMBER_OF_ASCII_CODES; $index++) {
            $character = CodePoints::codePoint($index);

            if ($this->contains($character)) {
                $asciiMembers[] = $character;
            }
        }

        $this->asciiMembers = $asciiMembers;

        $size = 0;

        foreach ($ranges as [$first, $last]) {
            $size += $last - $first + 1;
        }

        assert($size >= 0);

        $this->size = $size;
    }

    public function union(self $other): self
    {
        return self::fromRanges([...$this->ranges, ...$other->ranges], $this->utf8);
    }

    /**
     * The characters of $within that are not in this set.
     */
    public function complementWithin(self $within): self
    {
        $ranges = [];

        foreach ($within->ranges as [$first, $last]) {
            foreach ($this->ranges as [$excludedFirst, $excludedLast]) {
                if ($excludedLast < $first || $excludedFirst > $last) {
                    continue;
                }

                if ($excludedFirst > $first) {
                    $ranges[] = [$first, $excludedFirst - 1];
                }

                $first = $excludedLast + 1;

                if ($first > $last) {
                    break;
                }
            }

            if ($first <= $last) {
                $ranges[] = [$first, $last];
            }
        }

        return new self($ranges, $this->utf8);
    }

    public function contains(int $character): bool
    {
        foreach ($this->ranges as [$first, $last]) {
            if ($character >= $first && $character <= $last) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * @return non-negative-int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfAsciiMembers(): int
    {
        return count($this->asciiMembers);
    }

    /**
     * Returns the member with the given index in the order from the simplest
     * to the least simple character, encoded as UTF-8 or as a byte.
     *
     * @param non-negative-int $index a number smaller than the size of the set
     *
     * @return non-empty-string
     */
    public function member(int $index): string
    {
        assert($index < $this->size);

        if ($index < count($this->asciiMembers)) {
            return $this->encode($this->asciiMembers[$index]);
        }

        $index -= count($this->asciiMembers);
        $character = 0;

        foreach ($this->ranges as [$first, $last]) {
            $first = max($first, self::NUMBER_OF_ASCII_CODES);

            if ($first > $last) {
                continue;
            }

            $character = $first + $index;

            if ($character <= $last) {
                break;
            }

            $index -= $last - $first + 1;
        }

        assert($character >= 0);

        return $this->encode($character);
    }

    /**
     * @param non-negative-int $character
     *
     * @return non-empty-string
     */
    private function encode(int $character): string
    {
        if (!$this->utf8) {
            $byte = chr(min($character, self::LARGEST_BYTE));

            assert($byte !== '');

            return $byte;
        }

        $encoded = mb_chr($character, 'UTF-8');

        assert($encoded !== false && $encoded !== '');

        return $encoded;
    }
}
