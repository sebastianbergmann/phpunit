<?php declare(strict_types=1);
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Diff;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;
use function array_any;
use function array_unshift;
use function array_values;
use function count;
use function current;
use function end;
use function is_string;
use function key;
use function preg_split;
use function prev;
use function reset;
use function str_ends_with;
use function substr;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;

final class Differ
{
    public const int OLD                     = 0;
    public const int ADDED                   = 1;
    public const int REMOVED                 = 2;
    public const int DIFF_LINE_END_WARNING   = 3;
    public const int NO_LINE_END_EOF_WARNING = 4;
    private DiffOutputBuilderInterface $outputBuilder;

    public function __construct(DiffOutputBuilderInterface $outputBuilder)
    {
        $this->outputBuilder = $outputBuilder;
    }

    /**
     * @param array<int|string, int|string>|string $from
     * @param array<int|string, int|string>|string $to
     */
    public function diff(array|string $from, array|string $to): string
    {
        $diff = $this->diffToArray($from, $to);

        return $this->outputBuilder->getDiff($diff);
    }

    /**
     * @param array<int|string, int|string>|string $from
     * @param array<int|string, int|string>|string $to
     *
     * @return list<array{0: mixed, 1: int}>
     */
    public function diffToArray(array|string $from, array|string $to): array
    {
        if (is_string($from)) {
            $from = $this->splitStringByLines($from);
        }

        if (is_string($to)) {
            $to = $this->splitStringByLines($to);
        }

        [$from, $to, $start, $end] = self::getArrayDiffParted($from, $to);

        $diff = [];

        foreach ($start as $token) {
            $diff[] = [$token, self::OLD];
        }

        foreach ((new MyersDiff)->calculate(array_values($from), array_values($to)) as $entry) {
            $diff[] = $entry;
        }

        foreach ($end as $token) {
            $diff[] = [$token, self::OLD];
        }

        if ($this->detectUnmatchedLineEndings($diff)) {
            array_unshift($diff, ["#Warning: Strings contain different line endings!\n", self::DIFF_LINE_END_WARNING]);
        }

        return $diff;
    }

    /**
     * @return list<string>
     */
    private function splitStringByLines(string $input): array
    {
        $result = preg_split('/(.*\R)/', $input, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($result === false) {
            return [];
        }

        return $result;
    }

    /**
     * @param list<array{0: mixed, 1: int}> $diff
     */
    private function detectUnmatchedLineEndings(array $diff): bool
    {
        $newLineBreaks = ['' => true];
        $oldLineBreaks = ['' => true];

        foreach ($diff as $entry) {
            if (self::OLD === $entry[1]) {
                /** @phpstan-ignore argument.type */
                $ln                 = $this->getLinebreak($entry[0]);
                $oldLineBreaks[$ln] = true;
                $newLineBreaks[$ln] = true;
            } elseif (self::ADDED === $entry[1]) {
                /** @phpstan-ignore argument.type */
                $newLineBreaks[$this->getLinebreak($entry[0])] = true;
            } elseif (self::REMOVED === $entry[1]) {
                /** @phpstan-ignore argument.type */
                $oldLineBreaks[$this->getLinebreak($entry[0])] = true;
            }
        }

        // if either input or output is a single line without breaks than no warning should be raised
        if (count($newLineBreaks) === 1 || count($oldLineBreaks) === 1) {
            return false;
        }

        // two-way compare
        if (array_any($newLineBreaks, static fn (bool $set, string $break) => !isset($oldLineBreaks[$break]))) {
            return true;
        }

        return array_any($oldLineBreaks, static fn (bool $set, string $break) => !isset($newLineBreaks[$break]));
    }

    private function getLinebreak(int|string $line): string
    {
        if (!is_string($line)) {
            return '';
        }

        $lc = substr($line, -1);

        if ("\r" === $lc) {
            return "\r";
        }

        if ("\n" !== $lc) {
            return '';
        }

        if (str_ends_with($line, "\r\n")) {
            return "\r\n";
        }

        return "\n";
    }

    /**
     * @param array<int|string, int|string> $from
     * @param array<int|string, int|string> $to
     *
     * @return array{0: array<int|string, int|string>, 1: array<int|string, int|string>, 2: array<int|string, int|string>, 3: array<int|string, int|string>}
     */
    private static function getArrayDiffParted(array &$from, array &$to): array
    {
        $start = [];
        $end   = [];

        reset($to);

        foreach ($from as $k => $v) {
            $toK = key($to);

            /** @phpstan-ignore offsetAccess.notFound */
            if ($toK === $k && $v === $to[$k]) {
                $start[$k] = $v;

                unset($from[$k], $to[$k]);
            } else {
                break;
            }
        }

        end($from);
        end($to);

        do {
            $fromK = key($from);
            $toK   = key($to);

            if (null === $fromK || null === $toK || current($from) !== current($to)) {
                break;
            }

            prev($from);
            prev($to);

            /** @phpstan-ignore offsetAccess.notFound */
            $end = [$fromK => $from[$fromK]] + $end;
            unset($from[$fromK], $to[$toK]);
        } while (true);

        return [$from, $to, $start, $end];
    }
}
