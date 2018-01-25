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

/**
 * Constraint that asserts that the string it is evaluated for matches
 * a regular expression.
 *
 * Checks a given value using the Perl Compatible Regular Expression extension
 * in PHP. The pattern is matched by executing preg_match().
 *
 * The pattern string passed in the constructor.
 */
class RegularExpression extends Constraint
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        parent::__construct();

        $this->pattern = $pattern;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        return \sprintf(
            'matches PCRE pattern "%s"',
            $this->pattern
        );
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
        return \preg_match($this->pattern, $other) > 0;
    }
}
