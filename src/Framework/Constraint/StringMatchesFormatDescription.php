<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use SebastianBergmann\Diff\Differ;

/**
 * ...
 */
class StringMatchesFormatDescription extends RegularExpression
{
    /**
     * @var string
     */
    private $string;

    public function __construct(string $string)
    {
        parent::__construct(
            $this->createPatternFromFormat(
                $this->convertNewlines($string)
            )
        );

        $this->string = $string;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        return parent::matches(
            $this->convertNewlines($other)
        );
    }

    protected function failureDescription($other): string
    {
        return 'string matches format description';
    }

    protected function additionalFailureDescription($other): string
    {
        $from = \explode(PHP_EOL, $this->string);
        $to   = \explode(PHP_EOL, $this->convertNewlines($other));

        foreach ($from as $index => $line) {
            if (isset($to[$index]) && $line !== $to[$index]) {
                $line = $this->createPatternFromFormat($line);

                if (\preg_match($line, $to[$index]) > 0) {
                    $from[$index] = $to[$index];
                }
            }
        }

        $this->string = \implode(PHP_EOL, $from);
        $other        = \implode(PHP_EOL, $to);

        $differ = new Differ("--- Expected\n+++ Actual\n");

        return $differ->diff($this->string, $other);
    }

    private function createPatternFromFormat(string $string): string
    {
        $string = \preg_replace(
            [
                '/(?<!%)%e/',
                '/(?<!%)%s/',
                '/(?<!%)%S/',
                '/(?<!%)%a/',
                '/(?<!%)%A/',
                '/(?<!%)%w/',
                '/(?<!%)%i/',
                '/(?<!%)%d/',
                '/(?<!%)%x/',
                '/(?<!%)%f/',
                '/(?<!%)%c/'
            ],
            [
                \str_replace('\\', '\\\\', '\\' . DIRECTORY_SEPARATOR),
                '[^\r\n]+',
                '[^\r\n]*',
                '.+',
                '.*',
                '\s*',
                '[+-]?\d+',
                '\d+',
                '[0-9a-fA-F]+',
                '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?',
                '.'
            ],
            \preg_quote($string, '/')
        );

        $string = \str_replace('%%', '%', $string);

        return '/^' . $string . '$/s';
    }

    private function convertNewlines($text): string
    {
        return \preg_replace('/\r\n/', PHP_EOL, $text);
    }
}
