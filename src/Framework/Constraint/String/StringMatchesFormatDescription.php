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

    protected function additionalFailureDescription(mixed $other): string
    {
        $from = explode("\n", $this->formatDescription);
        $to   = explode("\n", $this->convertNewlines($other));

        foreach ($from as $index => $line) {
            if (isset($to[$index]) && $line !== $to[$index]) {
                $line = $this->regularExpressionForFormatDescription($line);

                if (preg_match($line, $to[$index]) > 0) {
                    $from[$index] = $to[$index];
                }
            }
        }

        $from = implode("\n", $from);
        $to   = implode("\n", $to);

        return $this->differ()->diff($from, $to);
    }

    private function regularExpressionForFormatDescription(string $string): string
    {
        $string = strtr(
            preg_quote($string, '/'),
            [
                '%%' => '%', // A literal percent character
                '%e' => preg_quote(DIRECTORY_SEPARATOR, '/'), // a directory separator
                '%s' => '[^\r\n]+', // One or more of anything except the end of line character
                '%S' => '[^\r\n]*', // Zero or more of anything except the end of line character
                '%a' => '.+?', // One or more of anything including the end of line character
                '%A' => '.*?', // Zero or more of anything including the end of line character
                '%w' => '\s*', // Zero or more white space characters
                '%i' => '[+-]?\d+', // A signed integer value
                '%d' => '\d+', // An unsigned integer value
                '%x' => '[0-9a-fA-F]+', // One or more hexadecimal character
                '%f' => '[+-]?(?:\d+|(?=\.\d))(?:\.\d+)?(?:[Ee][+-]?\d+)?', // A floating point number
                '%c' => '.', // A single character of any sort
                '%0' => '\x00', // A null byte
            ],
        );

        return '/^' . $string . '$/s';
    }

    private function convertNewlines(string $text): string
    {
        return preg_replace('/\r\n/', "\n", $text);
    }

    private function differ(): Differ
    {
        return new Differ(new UnifiedDiffOutputBuilder("--- Expected\n+++ Actual\n"));
    }
}
