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

use const PREG_UNMATCHED_AS_NULL;
use function array_pop;
use function count;
use function max;
use function preg_match;
use function preg_split;

/**
 * Unified diff parser.
 */
final class Parser
{
    private const string LINE_BREAK       = '(\r\n|\r|\n)';
    private const string FROM_FILE_HEADER = '#^---\h+"?(?P<file>[^\\v\\t"]+)#';
    private const string TO_FILE_HEADER   = '#^\\+\\+\\+\\h+"?(?P<file>[^\\v\\t"]+)#';
    private const string METADATA_HEADER  = '/^(?:diff --git |index [\da-f.]+|(?:---|\+\+\+) [ab]\/)/';
    private const string CHUNK_HEADER     = '/^@@\s+-(?P<start>\d+)(?:,\s*(?P<startrange>\d+))?\s+\+(?P<end>\d+)(?:,\s*(?P<endrange>\d+))?\s+@@/';
    private const string CHUNK_LINE       = '/^(?P<type>[+ -])?(?P<line>.*)/';

    /**
     * @return list<Diff>
     */
    public function parse(string $string): array
    {
        $lines = preg_split(self::LINE_BREAK, $string);

        if ($lines === false) {
            return [];
        }

        if ($lines !== [] && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }

        $lineCount     = count($lines);
        $diffs         = [];
        $diff          = null;
        $collected     = [];
        $fromLinesLeft = 0;
        $toLinesLeft   = 0;

        for ($i = 0; $i < $lineCount; $i++) {
            if (!isset($lines[$i])) {
                continue;
            }

            $line     = $lines[$i];
            $nextLine = $lines[$i + 1] ?? null;

            if ($fromLinesLeft > 0 || $toLinesLeft > 0) {
                $marker = $line === '' ? ' ' : $line[0];

                if ($marker === ' ' || $marker === '+' || $marker === '-' || $marker === '\\') {
                    $collected[] = $line;

                    if ($marker !== '+' && $marker !== '\\') {
                        $fromLinesLeft--;
                    }

                    if ($marker !== '-' && $marker !== '\\') {
                        $toLinesLeft--;
                    }

                    continue;
                }

                $fromLinesLeft = 0;
                $toLinesLeft   = 0;
            }

            if (preg_match(self::CHUNK_HEADER, $line, $chunkMatch, PREG_UNMATCHED_AS_NULL) === 1) {
                $fromLinesLeft = isset($chunkMatch['startrange']) ? max(0, (int) $chunkMatch['startrange']) : 1;
                $toLinesLeft   = isset($chunkMatch['endrange']) ? max(0, (int) $chunkMatch['endrange']) : 1;

                $collected[] = $line;

                continue;
            }

            if ($nextLine !== null &&
                preg_match(self::FROM_FILE_HEADER, $line, $fromMatch) === 1 &&
                preg_match(self::TO_FILE_HEADER, $nextLine, $toMatch) === 1) {
                if ($diff !== null) {
                    $this->parseFileDiff($diff, $collected);

                    $diffs[]   = $diff;
                    $collected = [];
                }

                $diff = new Diff($fromMatch['file'], $toMatch['file']);

                $i++;

                continue;
            }

            if (preg_match(self::METADATA_HEADER, $line) === 1) {
                continue;
            }

            $collected[] = $line;
        }

        if ($diff !== null && $collected !== []) {
            $this->parseFileDiff($diff, $collected);

            $diffs[] = $diff;
        }

        return $diffs;
    }

    /**
     * @param string[] $lines
     */
    private function parseFileDiff(Diff $diff, array $lines): void
    {
        $chunks    = [];
        $chunk     = null;
        $diffLines = [];

        foreach ($lines as $line) {
            if (preg_match(self::CHUNK_HEADER, $line, $match, PREG_UNMATCHED_AS_NULL) === 1) {
                $chunk = new Chunk(
                    (int) $match['start'],
                    isset($match['startrange']) ? max(0, (int) $match['startrange']) : 1,
                    (int) $match['end'],
                    isset($match['endrange']) ? max(0, (int) $match['endrange']) : 1,
                );

                $chunks[]  = $chunk;
                $diffLines = [];

                continue;
            }

            if (preg_match(self::CHUNK_LINE, $line, $match) === 1) {
                $type = Line::UNCHANGED;

                if ($match['type'] === '+') {
                    $type = Line::ADDED;
                } elseif ($match['type'] === '-') {
                    $type = Line::REMOVED;
                }

                $diffLines[] = new Line($type, $match['line']);

                $chunk?->setLines($diffLines);
            }
        }

        $diff->setChunks($chunks);
    }
}
