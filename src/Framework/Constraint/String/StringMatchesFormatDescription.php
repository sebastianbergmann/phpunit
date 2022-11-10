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
use function explode;
use function implode;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function sprintf;
use function strtr;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class StringMatchesFormatDescription extends Constraint
{
    private string $formatDescription;
    private readonly string $regularExpression;

    public function __construct(string $formatDescription)
    {
        $this->regularExpression = $this->createRegularExpressionFromFormatDescription(
            $this->convertNewlines($formatDescription)
        );

        $this->formatDescription = $formatDescription;
    }

    /**
     * @todo Use format description instead of regular expression
     */
    public function toString(): string
    {
        return sprintf(
            'matches PCRE pattern "%s"',
            $this->regularExpression
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        $other = $this->convertNewlines($other);

        return preg_match($this->regularExpression, $other) > 0;
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
                $line = $this->createRegularExpressionFromFormatDescription($line);

                if (preg_match($line, $to[$index]) > 0) {
                    $from[$index] = $to[$index];
                }
            }
        }

        $this->formatDescription = implode("\n", $from);
        $other                   = implode("\n", $to);

        return (new Differ(new UnifiedDiffOutputBuilder("--- Expected\n+++ Actual\n")))->diff($this->formatDescription, $other);
    }

    private function createRegularExpressionFromFormatDescription(string $string): string
    {
        $string = strtr(
            preg_quote($string, '/'),
            [
                '%%' => '%',
                '%e' => '\\' . DIRECTORY_SEPARATOR,
                '%s' => '[^\r\n]+',
                '%S' => '[^\r\n]*',
                '%a' => '.+',
                '%A' => '.*',
                '%w' => '\s*',
                '%i' => '[+-]?\d+',
                '%d' => '\d+',
                '%x' => '[0-9a-fA-F]+',
                '%f' => '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?',
                '%c' => '.',
            ]
        );

        return '/^' . $string . '$/s';
    }

    private function convertNewlines(string $text): string
    {
        return preg_replace('/\r\n/', "\n", $text);
    }
}
