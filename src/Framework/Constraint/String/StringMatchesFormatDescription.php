<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use function assert;
use function count;
use function explode;
use function implode;
use function preg_last_error_msg;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strtr;
use function substr;
use PHPUnit\Framework\Exception as FrameworkException;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class StringMatchesFormatDescription extends Constraint
{
    private readonly string $formatDescription;

    public function __construct(string $formatDescription)
    {
        $this->formatDescription = $formatDescription;
    }

    public function toString(): string
    {
        return 'matches format description:' . PHP_EOL . $this->formatDescription;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws FrameworkException
     */
    protected function matches(mixed $other): bool
    {
        $other = $this->convertNewlines($other);

        $matches = @preg_match(
            $this->regularExpressionForFormatDescription(
                $this->convertNewlines($this->formatDescription),
            ),
            $other,
        );

        if ($matches === false) {
            throw new FrameworkException(
                sprintf(
                    'Format description cannot be matched: %s',
                    preg_last_error_msg(),
                ),
            );
        }

        return $matches > 0;
    }

    protected function failureDescription(mixed $other): string
    {
        return 'string matches format description';
    }

    /**
     * Returns a cleaned up diff.
     *
     * The expected string can contain placeholders like %s and %d.
     * By using 'diff' such placeholders compared to the real output will
     * always be different, although we don't want to show them as different.
     * This method removes the expected differences by figuring out if a difference
     * is allowed by the use of a placeholder.
     *
     * For %A and %a multiline placeholders that can match across multiple lines,
     * we use anchor lookahead: find the next non-multiline expected line and search
     * for it in the actual output to determine how many actual lines the placeholder
     * consumed, keeping expected and actual in sync.
     */
    protected function additionalFailureDescription(mixed $other): string
    {
        $expected      = explode("\n", $this->formatDescription);
        $expectedCount = count($expected);
        $actual        = explode("\n", $this->convertNewlines($other));
        $actualCount   = count($actual);
        $synced        = [];
        $expectedIndex = 0;
        $actualIndex   = 0;

        while ($expectedIndex < $expectedCount && $actualIndex < $actualCount) {
            if ($expected[$expectedIndex] === $actual[$actualIndex]) {
                $synced[] = $actual[$actualIndex];

                $expectedIndex++;
                $actualIndex++;

                continue;
            }

            if ($this->isMultilineMatch($expected[$expectedIndex])) {
                $anchorExpectedIndex = $this->findNextAnchor($expected, $expectedIndex + 1);

                if ($anchorExpectedIndex !== null) {
                    $anchorActualIndex = $this->findAnchorInActual($expected[$anchorExpectedIndex], $actual, $actualIndex);

                    if ($anchorActualIndex !== null) {
                        for ($i = $actualIndex; $i < $anchorActualIndex; $i++) {
                            $synced[] = $actual[$i];
                        }

                        $synced[] = $actual[$anchorActualIndex];

                        $expectedIndex = $anchorExpectedIndex + 1;
                        $actualIndex   = $anchorActualIndex + 1;

                        continue;
                    }
                } else {
                    // No anchor after multiline placeholder(s): consume all remaining actual lines
                    for ($i = $actualIndex; $i < $actualCount; $i++) {
                        $synced[] = $actual[$i];
                    }

                    $expectedIndex = $expectedCount;
                    $actualIndex   = $actualCount;

                    continue;
                }
            }

            // Single-line comparison
            $regex = $this->regularExpressionForFormatDescription($expected[$expectedIndex]);

            if (@preg_match($regex, $actual[$actualIndex]) > 0) {
                $synced[] = $actual[$actualIndex];
            } else {
                $synced[] = $expected[$expectedIndex];
            }

            $expectedIndex++;
            $actualIndex++;
        }

        while ($expectedIndex < $expectedCount) {
            $synced[] = $expected[$expectedIndex];
            $expectedIndex++;
        }

        return $this->differ()->diff(implode("\n", $synced), implode("\n", $actual));
    }

    private function regularExpressionForFormatDescription(string $string): string
    {
        $quoted      = '';
        $startOffset = 0;
        $length      = strlen($string);

        while ($startOffset < $length) {
            $start = strpos($string, '%r', $startOffset);

            if ($start !== false) {
                $end = strpos($string, '%r', $start + 2);

                if ($end === false) {
                    $end = $start = $length;
                }
            } else {
                $start = $end = $length;
            }

            $quoted .= preg_quote(substr($string, $startOffset, $start - $startOffset), '/');

            if ($end > $start) {
                $quoted .= '(' . substr($string, $start + 2, $end - $start - 2) . ')';
            }

            $startOffset = $end + 2;
        }

        $string = strtr(
            $quoted,
            [
                '%%' => '%',
                '%e' => preg_quote(DIRECTORY_SEPARATOR, '/'),
                '%s' => '[^\r\n]+',
                '%S' => '[^\r\n]*',
                '%a' => '.+?',
                '%A' => '.*?',
                '%w' => '\s*',
                '%i' => '[+-]?\d+',
                '%d' => '\d+',
                '%x' => '[0-9a-fA-F]+',
                '%f' => '[+-]?(?:\d+|(?=\.\d))(?:\.\d+)?(?:[Ee][+-]?\d+)?',
                '%c' => '.',
                '%0' => '\x00',
            ],
        );

        return '/^' . $string . '$/s';
    }

    private function isMultilineMatch(string $line): bool
    {
        return preg_match('/%[aA]/', str_replace('%%', '', $line)) > 0;
    }

    /**
     * @param list<string> $expected
     */
    private function findNextAnchor(array $expected, int $startIdx): ?int
    {
        for ($i = $startIdx, $len = count($expected); $i < $len; $i++) {
            if (!$this->isMultilineMatch($expected[$i])) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param list<string> $actual
     */
    private function findAnchorInActual(string $anchorLine, array $actual, int $startIdx): ?int
    {
        $anchorRegex = $this->regularExpressionForFormatDescription($anchorLine);

        for ($i = $startIdx, $len = count($actual); $i < $len; $i++) {
            if ($anchorLine === $actual[$i] || @preg_match($anchorRegex, $actual[$i]) > 0) {
                return $i;
            }
        }

        return null;
    }

    private function convertNewlines(string $text): string
    {
        $result = preg_replace('/\r\n/', "\n", $text);

        assert($result !== null);

        return $result;
    }

    private function differ(): Differ
    {
        return new Differ(new UnifiedDiffOutputBuilder("--- Expected\n+++ Actual\n"));
    }
}
