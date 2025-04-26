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
use function array_slice;
use function array_splice;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function strtr;
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
     */
    protected function matches(mixed $other): bool
    {
        $other = $this->convertNewlines($other);

        $matches = preg_match(
            $this->regularExpressionForFormatDescription(
                $this->convertNewlines($this->formatDescription),
            ),
            $other,
        );

        return $matches > 0;
    }

    protected function failureDescription(mixed $other): string
    {
        return 'string matches format description';
    }

    /**
     * Returns a useful diff with the 'actual' differences.
     *
     * The expected string can contain placeholders like %s and %d.
     * By using 'diff' such placeholders compared to the real output are
     * always objectively different, although we don't want to show them as different.
     *
     * This method removes the objective differences by figuring out if an objective
     * difference is allowed by a placeholder.
     *
     * The final result should only contain the differences that caused the failing test.
     */
    protected function additionalFailureDescription(mixed $other): string
    {
        $expected = explode("\n", $this->formatDescription);
        $output   = explode("\n", $this->convertNewlines($other));

        for ($oIndex = 0, $eIndex = 0, $length = count($output); $oIndex < $length; $oIndex++) {
            $multiLineMatch = false;

            if (isset($expected[$eIndex]) && $expected[$eIndex] !== $output[$oIndex]) {
                $regEx     = $this->regularExpressionForFormatDescription($expected[$eIndex]);
                $compareTo = $output[$oIndex];
                $matches   = [];

                // if we do a multiline match we have to consider all following lines as well
                if ($this->isMultilineMatch($expected[$eIndex])) {
                    $multiLineMatch = true;
                    $compareTo      = implode("\n", array_slice($output, $oIndex));
                }

                if (preg_match($regEx, $compareTo, $matches) > 0) {
                    $lines = 1;

                    // if we matched multiple lines we have to sync $expected and $output
                    if ($multiLineMatch) {
                        $lines = count(explode("\n", $matches[0]));
                    }

                    // we sync at least one line
                    $expected[$eIndex] = $output[$oIndex];

                    // for multiline matches we sync the matched lines to $expected
                    for ($i = 1; $i < $lines; $i++) {
                        $eIndex++;
                        $oIndex++;

                        array_splice($expected, $eIndex, 0, [$output[$oIndex]]);
                    }
                }
            }

            $eIndex++;
        }

        $expectedString = implode("\n", $expected);
        $outputString   = implode("\n", $output);

        return $this->differ()->diff($expectedString, $outputString);
    }

    private function regularExpressionForFormatDescription(string $string): string
    {
        // only add the end of string check ($) for single line comparisons
        $endOfLine = $this->isMultilineMatch($string) ? '' : '$';
        $string    = strtr(
            preg_quote($string, '/'),
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

        return '/^' . $string . $endOfLine . '/s';
    }

    private function convertNewlines(string $text): string
    {
        return preg_replace('/\r\n/', "\n", $text);
    }

    private function differ(): Differ
    {
        return new Differ(new UnifiedDiffOutputBuilder("--- Expected\n+++ Actual\n"));
    }

    private function isMultilineMatch(string $line): bool
    {
        return preg_match('#%a#i', $line) > 0;
    }
}
