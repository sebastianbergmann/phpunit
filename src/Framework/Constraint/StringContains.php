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
 * Constraint that asserts that the string it is evaluated for contains
 * a given string.
 *
 * Uses mb_strpos() to find the position of the string in the input, if not
 * found the evaluation fails.
 *
 * The sub-string is passed in the constructor.
 */
class StringContains extends Constraint
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var bool
     */
    private $ignoreCase;

    /**
     * @param string $string
     * @param bool   $ignoreCase
     */
    public function __construct($string, $ignoreCase = false)
    {
        parent::__construct();

        $this->string     = $string;
        $this->ignoreCase = $ignoreCase;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        if ($this->ignoreCase) {
            $string = \mb_strtolower($this->string);
        } else {
            $string = $this->string;
        }

        return \sprintf(
            'contains "%s"',
            $string
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
        if ('' === $this->string) {
            return true;
        }

        if ($this->ignoreCase) {
            return \mb_stripos($other, $this->string) !== false;
        }

        return \mb_strpos($other, $this->string) !== false;
    }
}
