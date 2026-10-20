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

use function array_fill;
use function count;
use function intdiv;

/**
 * Linear-space variant of the Myers diff algorithm (Eugene W. Myers, 1986,
 * "An O(ND) Difference Algorithm and Its Variations", §4b).
 *
 * Time:   O((N + M) * D)
 * Space:  O(N + M)
 *
 * Where N and M are the input lengths and D is the edit distance.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class MyersDiff
{
    /**
     * @param list<mixed> $from
     * @param list<mixed> $to
     *
     * @return list<array{0: mixed, 1: int}> edit script as [token, Differ::OLD|ADDED|REMOVED] entries
     */
    public function calculate(array $from, array $to): array
    {
        $diff = [];

        $this->diffRange($from, 0, count($from), $to, 0, count($to), $diff);

        return $this->normalize($diff);
    }

    /**
     * Within each contiguous run of non-matching entries, emit all REMOVED
     * tokens before any ADDED tokens. The Myers algorithm may interleave them
     * depending on path tie-breaking; normalizing matches the conventional
     * diff(1) output ordering.
     *
     * @param list<array{0: mixed, 1: int}> $diff
     *
     * @return list<array{0: mixed, 1: int}>
     */
    private function normalize(array $diff): array
    {
        $result = [];
        $i      = 0;
        $n      = count($diff);

        while ($i < $n) {
            /** @phpstan-ignore offsetAccess.notFound */
            if ($diff[$i][1] === Differ::OLD) {
                $result[] = $diff[$i];
                $i++;

                continue;
            }

            $adds = [];

            /** @phpstan-ignore offsetAccess.notFound */
            while ($i < $n && $diff[$i][1] !== Differ::OLD) {
                if ($diff[$i][1] === Differ::REMOVED) {
                    $result[] = $diff[$i];
                } else {
                    $adds[] = $diff[$i];
                }

                $i++;
            }

            foreach ($adds as $entry) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    /**
     * @param list<mixed>                   $a
     * @param list<mixed>                   $b
     * @param list<array{0: mixed, 1: int}> $out
     */
    private function diffRange(array $a, int $aLo, int $aHi, array $b, int $bLo, int $bHi, array &$out): void
    {
        // Trim common prefix.
        // $aLo < $aHi <= count($a) and $bLo < $bHi <= count($b) ensure offsets exist.
        /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
        while ($aLo < $aHi && $bLo < $bHi && $a[$aLo] === $b[$bLo]) {
            $out[] = [$a[$aLo], Differ::OLD];
            $aLo++;
            $bLo++;
        }

        // Trim common suffix; defer emission until after the middle is processed.
        $suffix = [];

        /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
        while ($aLo < $aHi && $bLo < $bHi && $a[$aHi - 1] === $b[$bHi - 1]) {
            $aHi--;
            $bHi--;

            /** @phpstan-ignore offsetAccess.notFound */
            $suffix[] = [$a[$aHi], Differ::OLD];
        }

        $n = $aHi - $aLo;
        $m = $bHi - $bLo;

        if ($n === 0) {
            for ($j = $bLo; $j < $bHi; $j++) {
                /** @phpstan-ignore offsetAccess.notFound */
                $out[] = [$b[$j], Differ::ADDED];
            }
        } elseif ($m === 0) {
            for ($i = $aLo; $i < $aHi; $i++) {
                /** @phpstan-ignore offsetAccess.notFound */
                $out[] = [$a[$i], Differ::REMOVED];
            }
        } else {
            // Both n > 0 and m > 0 here, so the edit distance is at least 2
            // (one deletion plus one insertion); a single edit would have been
            // fully consumed by the prefix/suffix trimming above.
            [$xs, $ys, $xe, $ye] = $this->findMiddleSnake($a, $aLo, $aHi, $b, $bLo, $bHi);

            $this->diffRange($a, $aLo, $aLo + $xs, $b, $bLo, $bLo + $ys, $out);

            for ($i = $xs; $i < $xe; $i++) {
                /** @phpstan-ignore offsetAccess.notFound */
                $out[] = [$a[$aLo + $i], Differ::OLD];
            }

            $this->diffRange($a, $aLo + $xe, $aHi, $b, $bLo + $ye, $bHi, $out);
        }

        // Emit the deferred common suffix in original order.
        for ($i = count($suffix) - 1; $i >= 0; $i--) {
            /** @phpstan-ignore offsetAccess.notFound */
            $out[] = $suffix[$i];
        }
    }

    /**
     * Find the middle snake of an optimal D-path through the edit graph using
     * bidirectional search.
     *
     * @param list<mixed> $a
     * @param list<mixed> $b
     *
     * @return array{0: int, 1: int, 2: int, 3: int} [xStart, yStart, xEnd, yEnd] in coords relative to (aLo, bLo)
     */
    private function findMiddleSnake(array $a, int $aLo, int $aHi, array $b, int $bLo, int $bHi): array
    {
        // All $vf, $vb offsets below resolve to indices within [0, $size) by the algorithm's
        // invariants (|$k| <= $d <= $maxD, $offset = $maxD + 1). The $a, $b accesses are guarded
        // by $x < $n, $y < $m bounds and $aHi <= count($a), $bHi <= count($b) by construction.
        $n          = $aHi - $aLo;
        $m          = $bHi - $bLo;
        $delta      = $n - $m;
        $deltaIsOdd = ($delta & 1) !== 0;
        $maxD       = intdiv($n + $m + 1, 2);
        $offset     = $maxD + 1;
        $size       = 2 * $maxD + 2;
        $vf         = array_fill(0, $size, 0);
        $vb         = array_fill(0, $size, 0);

        for ($d = 0; $d <= $maxD; $d++) {
            // Forward pass.
            for ($k = -$d; $k <= $d; $k += 2) {
                /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
                if ($k === -$d || ($k !== $d && $vf[$offset + $k - 1] < $vf[$offset + $k + 1])) {
                    /** @phpstan-ignore offsetAccess.notFound */
                    $x = $vf[$offset + $k + 1];
                } else {
                    /** @phpstan-ignore offsetAccess.notFound */
                    $x = $vf[$offset + $k - 1] + 1;
                }

                $y  = $x - $k;
                $xs = $x;
                $ys = $y;

                /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
                while ($x < $n && $y < $m && $a[$aLo + $x] === $b[$bLo + $y]) {
                    $x++;
                    $y++;
                }

                $vf[$offset + $k] = $x;

                if ($deltaIsOdd) {
                    $kb = $delta - $k;

                    /** @phpstan-ignore offsetAccess.notFound */
                    if ($kb >= -($d - 1) && $kb <= $d - 1 && $vf[$offset + $k] + $vb[$offset + $kb] >= $n) {
                        return [$xs, $ys, $x, $y];
                    }
                }
            }

            // Backward pass.
            for ($k = -$d; $k <= $d; $k += 2) {
                /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
                if ($k === -$d || ($k !== $d && $vb[$offset + $k - 1] < $vb[$offset + $k + 1])) {
                    /** @phpstan-ignore offsetAccess.notFound */
                    $x = $vb[$offset + $k + 1];
                } else {
                    /** @phpstan-ignore offsetAccess.notFound */
                    $x = $vb[$offset + $k - 1] + 1;
                }

                $y  = $x - $k;
                $xs = $x;
                $ys = $y;

                /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound */
                while ($x < $n && $y < $m && $a[$aLo + $n - 1 - $x] === $b[$bLo + $m - 1 - $y]) {
                    $x++;
                    $y++;
                }

                $vb[$offset + $k] = $x;

                if (!$deltaIsOdd) {
                    $kf = $delta - $k;

                    /** @phpstan-ignore offsetAccess.notFound */
                    if ($kf >= -$d && $kf <= $d && $vf[$offset + $kf] + $vb[$offset + $k] >= $n) {
                        return [$n - $x, $m - $y, $n - $xs, $m - $ys];
                    }
                }
            }
        }

        // @codeCoverageIgnoreStart
        throw new NoMiddleSnakeFoundException;
        // @codeCoverageIgnoreEnd
    }
}
