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

class ExceptionCode extends Constraint
{
    /**
     * @var int
     */
    private $expectedCode;

    /**
     * @param int $expected
     */
    public function __construct($expected)
    {
        parent::__construct();

        $this->expectedCode = $expected;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return 'exception code is ';
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
        return (string) $other->getCode() === (string) $this->expectedCode;
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s is equal to expected exception code %s',
            $this->exporter->export($other->getCode()),
            $this->exporter->export($this->expectedCode)
        );
    }
}
