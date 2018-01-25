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

class ExceptionMessage extends Constraint
{
    /**
     * @var string
     */
    private $expectedMessage;

    /**
     * @param string $expected
     */
    public function __construct($expected)
    {
        parent::__construct();

        $this->expectedMessage = $expected;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        if ($this->expectedMessage === '') {
            return 'exception message is empty';
        }

        return 'exception message contains ';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param \Throwable $other
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        if ($this->expectedMessage === '') {
            return $other->getMessage() === '';
        }

        return \strpos($other->getMessage(), $this->expectedMessage) !== false;
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        if ($this->expectedMessage === '') {
            return \sprintf(
                "exception message is empty but is '%s'",
                $other->getMessage()
            );
        }

        return \sprintf(
            "exception message '%s' contains '%s'",
            $other->getMessage(),
            $this->expectedMessage
        );
    }
}
