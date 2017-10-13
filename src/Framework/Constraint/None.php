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

class None extends Constraint
{
    /**
     * @var callable
     */
    protected $callable;

    public function __construct(callable $callable)
    {
        parent::__construct();
        $this->callable = $callable;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param array|\Traversable $other Array or Traversable object to evaluate.
     *
     * @return bool
     */
    public function matches($other)
    {
        $anyConstraint = new Any($this->callable);

        return !$anyConstraint->matches($other);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'callback function';
    }

    /**
     * @inheritdoc
     */
    protected function failureDescription($other)
    {
        return sprintf(
            'none of the elements of %s passes the test of the provided %s',
            $this->exporter->export($other),
            $this->toString()
        );
    }
}
