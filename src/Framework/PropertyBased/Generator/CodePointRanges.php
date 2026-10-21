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

use function array_is_list;
use function array_shift;
use function assert;
use function count;
use function implode;
use function is_array;
use function is_int;
use function max;
use function min;
use function sprintf;
use function usort;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;

/**
 * The code points of the characters that a string generator chooses from,
 * given as ranges. The ranges are sorted, ranges that overlap or adjoin each
 * other are merged, and surrogates, which are not characters, are left out.
 * The characters are numbered from the lowest code point, the simplest one,
 * to the highest one.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CodePointRanges
{
    private const int LARGEST_CODE_POINT = 0x10FFFF;
    private const int FIRST_SURROGATE    = 0xD800;
    private const int LAST_SURROGATE     = 0xDFFF;

    /**
     * The ranges, each with its first and its last code point, the number of
     * its code points, and the index of its first code point among all code
     * points.
     *
     * @var non-empty-list<array{first: int<0, 1114111>, last: int<0, 1114111>, size: positive-int, index: non-negative-int}>
     */
    private array $ranges;

    /**
     * @var positive-int
     */
    private int $numberOfCodePoints;

    /**
     * @param array<mixed> $ranges the ranges as lists of their first and their last code point
     *
     * @throws InvalidGeneratorArgumentException
     */
    public static function from(array $ranges): self
    {
        if ($ranges === []) {
            throw new InvalidGeneratorArgumentException('At least one code point range must be specified');
        }

        $valid = [];

        foreach ($ranges as $range) {
            [$first, $last] = self::bounds($range);

            // the surrogates are left out of a range that contains some of them
            if ($first < self::FIRST_SURROGATE) {
                $valid[] = ['first' => $first, 'last' => min($last, self::FIRST_SURROGATE - 1)];
            }

            if ($last > self::LAST_SURROGATE) {
                $valid[] = ['first' => max($first, self::LAST_SURROGATE + 1), 'last' => $last];
            }
        }

        if ($valid === []) {
            throw new InvalidGeneratorArgumentException('The code point ranges contain only surrogates, which are not characters');
        }

        usort(
            $valid,
            static fn (array $a, array $b): int => $a['first'] <=> $b['first'],
        );

        return new self($valid);
    }

    /**
     * @param non-empty-list<array{first: int<0, 1114111>, last: int<0, 1114111>}> $sorted the ranges, sorted by their first code point
     */
    private function __construct(array $sorted)
    {
        $ranges  = [];
        $current = array_shift($sorted);

        foreach ($sorted as $range) {
            if ($range['first'] <= $current['last'] + 1) {
                $current['last'] = max($current['last'], $range['last']);

                continue;
            }

            $ranges[] = $current;
            $current  = $range;
        }

        $ranges[] = $current;

        $index   = 0;
        $indexed = [];

        foreach ($ranges as $range) {
            // the first code point of a range is not greater than its last one
            $size = $range['last'] - $range['first'] + 1;

            assert($size > 0);

            $indexed[] = ['first' => $range['first'], 'last' => $range['last'], 'size' => $size, 'index' => $index];

            $index += $size;
        }

        $this->ranges             = $indexed;
        $this->numberOfCodePoints = $index;
    }

    /**
     * @return positive-int
     */
    public function numberOfCodePoints(): int
    {
        return $this->numberOfCodePoints;
    }

    /**
     * The code point with the index, counting from 0 for the lowest code point.
     * An index beyond the highest code point stands for the highest code
     * point, like a choice that exceeds its range.
     *
     * @param non-negative-int $index
     *
     * @return int<0, 1114111>
     */
    public function codePoint(int $index): int
    {
        $codePoint = 0;

        foreach ($this->ranges as $range) {
            $codePoint = max($range['first'], min($range['last'], $range['first'] + $index - $range['index']));

            if ($index - $range['index'] < $range['size']) {
                break;
            }
        }

        return $codePoint;
    }

    /**
     * The ranges of the indexes of the code points of each range.
     *
     * @return non-empty-list<array{minimum: non-negative-int, maximum: non-negative-int}>
     */
    public function indexRanges(): array
    {
        $indexRanges = [];

        foreach ($this->ranges as $range) {
            $indexRanges[] = ['minimum' => $range['index'], 'maximum' => $range['index'] + $range['size'] - 1];
        }

        return $indexRanges;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $ranges = [];

        foreach ($this->ranges as $range) {
            $ranges[] = sprintf('%X-%X', $range['first'], $range['last']);
        }

        return implode(',', $ranges);
    }

    /**
     * @throws InvalidGeneratorArgumentException
     *
     * @return array{0: int<0, 1114111>, 1: int<0, 1114111>}
     */
    private static function bounds(mixed $range): array
    {
        if (is_array($range) && array_is_list($range) && count($range) === 2) {
            [$first, $last] = $range;

            if (is_int($first) && is_int($last) && $first >= 0 && $last <= self::LARGEST_CODE_POINT && $first <= $last) {
                return [$first, $last];
            }
        }

        throw new InvalidGeneratorArgumentException(
            sprintf(
                'A code point range must be a list of its first and its last code point, from 0 to %d',
                self::LARGEST_CODE_POINT,
            ),
        );
    }
}
