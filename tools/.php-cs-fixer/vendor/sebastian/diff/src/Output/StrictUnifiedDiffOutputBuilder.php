<?php declare(strict_types=1);
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Diff\Output;

use function array_merge;
use function array_splice;
use function assert;
use function count;
use function fclose;
use function fopen;
use function fwrite;
use function is_bool;
use function is_int;
use function is_resource;
use function is_string;
use function max;
use function min;
use function sprintf;
use function str_ends_with;
use function stream_get_contents;
use function substr;
use SebastianBergmann\Diff\ConfigurationException;
use SebastianBergmann\Diff\Differ;

/**
 * Strict Unified diff output builder.
 *
 * Generates (strict) Unified diff's (unidiffs) with hunks.
 */
final class StrictUnifiedDiffOutputBuilder implements DiffOutputBuilderInterface
{
    /**
     * @var array{addLineNumbers: bool, collapseRanges: bool, commonLineThreshold: int, contextLines: int, emitDiffLineEndWarning: bool, emitNoLineEndEofWarning: bool, fromFile: null|string, fromFileDate: null|string, header: null|string, toFile: null|string, toFileDate: null|string}
     */
    private static array $default = [
        'addLineNumbers'          => true,  // when false, hunk header is rendered as `@@ @@` (no line numbers); when true, as `@@ -from,range +to,range @@`
        'collapseRanges'          => true,  // ranges of length one are rendered with the trailing `,1`
        'commonLineThreshold'     => 6,     // number of same lines before ending a new hunk and creating a new one (if needed)
        'contextLines'            => 3,     // like `diff:  -u, -U NUM, --unified[=NUM]`, for patch/git apply compatibility best to keep at least @ 3
        'emitDiffLineEndWarning'  => false, // when true, the `#Warning: Strings contain different line endings!` line inserted by `Differ` is rendered (it is not part of strict unified diff format)
        'emitNoLineEndEofWarning' => true,  // when false, the `\ No newline at end of file` marker is suppressed (useful for comparisons not related to files)
        'fromFile'                => null,
        'fromFileDate'            => null,
        'header'                  => null,  // when provided, used verbatim as the header; otherwise built from `fromFile`/`fromFileDate`/`toFile`/`toFileDate`
        'toFile'                  => null,
        'toFileDate'              => null,
    ];
    private bool $addLineNumbers;
    private bool $changed;
    private bool $collapseRanges;

    /**
     * @var positive-int
     */
    private int $commonLineThreshold;
    private bool $emitDiffLineEndWarning;
    private bool $emitNoLineEndEofWarning;
    private string $header;

    /**
     * @var int<0, max>
     */
    private int $contextLines;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $options = array_merge(self::$default, $options);

        if (!is_bool($options['addLineNumbers'])) {
            throw new ConfigurationException('addLineNumbers', 'a bool', $options['addLineNumbers']);
        }

        if (!is_bool($options['collapseRanges'])) {
            throw new ConfigurationException('collapseRanges', 'a bool', $options['collapseRanges']);
        }

        if (!is_int($options['contextLines']) || $options['contextLines'] < 0) {
            throw new ConfigurationException('contextLines', 'an int >= 0', $options['contextLines']);
        }

        if (!is_int($options['commonLineThreshold']) || $options['commonLineThreshold'] <= 0) {
            throw new ConfigurationException('commonLineThreshold', 'an int > 0', $options['commonLineThreshold']);
        }

        if (!is_bool($options['emitDiffLineEndWarning'])) {
            throw new ConfigurationException('emitDiffLineEndWarning', 'a bool', $options['emitDiffLineEndWarning']);
        }

        if (!is_bool($options['emitNoLineEndEofWarning'])) {
            throw new ConfigurationException('emitNoLineEndEofWarning', 'a bool', $options['emitNoLineEndEofWarning']);
        }

        if (null !== $options['header']) {
            $this->assertString($options, 'header');

            /** @phpstan-ignore assign.propertyType */
            $this->header = $options['header'];
        } else {
            $this->assertString($options, 'fromFile');
            $this->assertString($options, 'toFile');
            $this->assertStringOrNull($options, 'fromFileDate');
            $this->assertStringOrNull($options, 'toFileDate');

            $this->header = sprintf(
                "--- %s%s\n+++ %s%s\n",
                /** @phpstan-ignore argument.type */
                $options['fromFile'],
                /** @phpstan-ignore binaryOp.invalid */
                null === $options['fromFileDate'] ? '' : "\t" . $options['fromFileDate'],
                /** @phpstan-ignore argument.type */
                $options['toFile'],
                /** @phpstan-ignore binaryOp.invalid */
                null === $options['toFileDate'] ? '' : "\t" . $options['toFileDate'],
            );
        }

        $this->addLineNumbers          = $options['addLineNumbers'];
        $this->collapseRanges          = $options['collapseRanges'];
        $this->commonLineThreshold     = $options['commonLineThreshold'];
        $this->contextLines            = $options['contextLines'];
        $this->emitDiffLineEndWarning  = $options['emitDiffLineEndWarning'];
        $this->emitNoLineEndEofWarning = $options['emitNoLineEndEofWarning'];
    }

    /**
     * @param list<array{0: mixed, 1: int}> $diff
     */
    public function getDiff(array $diff): string
    {
        if (0 === count($diff)) {
            return '';
        }

        $this->changed = false;

        $buffer = fopen('php://memory', 'r+b');

        assert(is_resource($buffer));

        if ('' !== $this->header) {
            fwrite($buffer, $this->header);

            if (!str_ends_with($this->header, "\n")) {
                fwrite($buffer, "\n");
            }
        }

        $this->writeDiffHunks($buffer, $diff);

        if (!$this->changed) {
            fclose($buffer);

            return '';
        }

        $diff = stream_get_contents($buffer, -1, 0);

        fclose($buffer);

        // If the last char is not a linebreak: add it.
        // This might happen when both the `from` and `to` do not have a trailing linebreak
        $last = substr($diff, -1);

        return "\n" !== $last && "\r" !== $last
            ? $diff . "\n"
            : $diff;
    }

    /**
     * @param non-empty-list<array{0: mixed, 1: int}> $diff
     */
    private function writeDiffHunks(mixed $output, array $diff): void
    {
        assert(is_resource($output));

        // detect "No newline at end of file" and insert into `$diff` if needed

        $upperLimit = count($diff);

        /** @phpstan-ignore offsetAccess.notFound */
        if (0 === $diff[$upperLimit - 1][1]) {
            /** @phpstan-ignore argument.type */
            $lc = substr($diff[$upperLimit - 1][0], -1);

            if ("\n" !== $lc) {
                array_splice($diff, $upperLimit, 0, [["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING]]);
            }
        } else {
            // search back for the last `+` and `-` line,
            // check if it has a trailing linebreak, else add a warning under it
            $toFind = [1 => true, 2 => true];

            for ($i = $upperLimit - 1; $i >= 0; $i--) {
                /** @phpstan-ignore offsetAccess.notFound */
                if (isset($toFind[$diff[$i][1]])) {
                    unset($toFind[$diff[$i][1]]);

                    /** @phpstan-ignore argument.type */
                    $lc = substr($diff[$i][0], -1);

                    if ("\n" !== $lc) {
                        array_splice($diff, $i + 1, 0, [["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING]]);
                    }

                    if ($toFind === []) {
                        break;
                    }
                }
            }
        }

        // write hunks to output buffer

        $cutOff      = max($this->commonLineThreshold, $this->contextLines);
        $hunkCapture = false;
        $sameCount   = $toRange = $fromRange = 0;
        $toStart     = $fromStart = 1;

        foreach ($diff as $i => $entry) {
            if (0 === $entry[1]) { // same
                if (false === $hunkCapture) {
                    $fromStart++;
                    $toStart++;

                    continue;
                }

                $sameCount++;
                $toRange++;
                $fromRange++;

                if ($sameCount === $cutOff) {
                    $contextStartOffset = ($hunkCapture - $this->contextLines) < 0
                        ? $hunkCapture
                        : $this->contextLines;

                    // note: $contextEndOffset = $this->contextLines;
                    //
                    // because we never go beyond the end of the diff.
                    // with the cutoff/contextlines here the follow is never true;
                    //
                    // if ($i - $cutOff + $this->contextLines + 1 > \count($diff)) {
                    //    $contextEndOffset = count($diff) - 1;
                    // }
                    //
                    // ; that would be true for a trailing incomplete hunk case which is dealt with after this loop

                    $this->writeHunk(
                        $diff,
                        $hunkCapture - $contextStartOffset,
                        $i - $cutOff + $this->contextLines + 1,
                        $fromStart - $contextStartOffset,
                        $fromRange - $cutOff + $contextStartOffset + $this->contextLines,
                        $toStart - $contextStartOffset,
                        $toRange - $cutOff + $contextStartOffset + $this->contextLines,
                        $output,
                    );

                    $fromStart += $fromRange;
                    $toStart   += $toRange;

                    $hunkCapture = false;
                    $sameCount   = $toRange = $fromRange = 0;
                }

                continue;
            }

            $sameCount = 0;

            if ($entry[1] === Differ::NO_LINE_END_EOF_WARNING) {
                continue;
            }

            $this->changed = true;

            if (false === $hunkCapture) {
                $hunkCapture = $i;
            }

            if (Differ::ADDED === $entry[1]) { // added
                $toRange++;
            }

            if (Differ::REMOVED === $entry[1]) { // removed
                $fromRange++;
            }
        }

        if (false === $hunkCapture) {
            return;
        }

        // we end here when cutoff (commonLineThreshold) was not reached, but we were capturing a hunk,
        // do not render hunk till end automatically because the number of context lines might be less than the commonLineThreshold

        $contextStartOffset = $hunkCapture - $this->contextLines < 0
            ? $hunkCapture
            : $this->contextLines;

        // prevent trying to write out more common lines than there are in the diff _and_
        // do not write more than configured through the context lines
        $contextEndOffset = min($sameCount, $this->contextLines);

        $fromRange -= $sameCount;
        $toRange   -= $sameCount;

        assert(isset($i) && is_int($i));

        $this->writeHunk(
            $diff,
            $hunkCapture - $contextStartOffset,
            $i - $sameCount + $contextEndOffset + 1,
            $fromStart - $contextStartOffset,
            $fromRange + $contextStartOffset + $contextEndOffset,
            $toStart - $contextStartOffset,
            $toRange + $contextStartOffset + $contextEndOffset,
            $output,
        );
    }

    /**
     * @param list<array{0: mixed, 1: int}> $diff
     */
    private function writeHunk(
        array $diff,
        int $diffStartIndex,
        int $diffEndIndex,
        int $fromStart,
        int $fromRange,
        int $toStart,
        int $toRange,
        mixed $output
    ): void {
        assert(is_resource($output));

        if ($this->addLineNumbers) {
            fwrite($output, '@@ -' . $fromStart);

            if (!$this->collapseRanges || 1 !== $fromRange) {
                fwrite($output, ',' . $fromRange);
            }

            fwrite($output, ' +' . $toStart);

            if (!$this->collapseRanges || 1 !== $toRange) {
                fwrite($output, ',' . $toRange);
            }

            fwrite($output, " @@\n");
        } else {
            fwrite($output, "@@ @@\n");
        }

        for ($i = $diffStartIndex; $i < $diffEndIndex; $i++) {
            /** @phpstan-ignore offsetAccess.notFound */
            if ($diff[$i][1] === Differ::ADDED) {
                $this->changed = true;

                /** @phpstan-ignore binaryOp.invalid */
                fwrite($output, '+' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::REMOVED) {
                $this->changed = true;

                /** @phpstan-ignore binaryOp.invalid */
                fwrite($output, '-' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::OLD) {
                /** @phpstan-ignore binaryOp.invalid */
                fwrite($output, ' ' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::NO_LINE_END_EOF_WARNING) {
                /** @phpstan-ignore argument.type */
                fwrite($output, $this->emitNoLineEndEofWarning ? $diff[$i][0] : "\n");
            } elseif ($this->emitDiffLineEndWarning && $diff[$i][1] === Differ::DIFF_LINE_END_WARNING) {
                /** @phpstan-ignore binaryOp.invalid */
                fwrite($output, ' ' . $diff[$i][0]);
            }
            // else: unknown/invalid type or skipped warning - silently skip
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function assertString(array $options, string $option): void
    {
        $value = $options[$option] ?? null;

        if (!is_string($value)) {
            throw new ConfigurationException($option, 'a string', $value);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function assertStringOrNull(array $options, string $option): void
    {
        $value = $options[$option] ?? null;

        if (null !== $value && !is_string($value)) {
            throw new ConfigurationException($option, 'a string or <null>', $value);
        }
    }
}
