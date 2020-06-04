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

final class LogicalNot extends Unary
{
    public static function negate(string $string): string
    {
        $positives = [
            'contains ',
            'exists',
            'has ',
            'is ',
            'are ',
            'matches ',
            'starts with ',
            'ends with ',
            'reference ',
            'not not ',
        ];

        $negatives = [
            'does not contain ',
            'does not exist',
            'does not have ',
            'is not ',
            'are not ',
            'does not match ',
            'starts not with ',
            'ends not with ',
            'don\'t reference ',
            'not ',
        ];

        \preg_match('/(\'[\w\W]*\')([\w\W]*)("[\w\W]*")/i', $string, $matches);

        if (\count($matches) > 0) {
            $nonInput = $matches[2];

            $negatedString = \str_replace(
                $nonInput,
                \str_replace(
                    $positives,
                    $negatives,
                    $nonInput
                ),
                $string
            );
        } else {
            $negatedString = \str_replace(
                $positives,
                $negatives,
                $string
            );
        }

        return $negatedString;
    }

    /**
     * Returns the name of this operator.
     */
    public function operator(): string
    {
        return 'not';
    }

    /**
     * Returns this operator's precedence, as defined in
     * https://www.php.net/manual/en/language.operators.precedence.php
     */
    public function precedence(): int
    {
        return 5;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        return !$this->constraint()->evaluate($other, '', true);
    }

    /**
     * Applies additional transformation to strings returned by toString() or
     * failureDescription().
     */
    protected function transformString(string $string): string
    {
        return self::negate($string);
    }
}
