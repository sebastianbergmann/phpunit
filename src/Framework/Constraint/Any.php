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

class Any extends Constraint
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
        $other    = $this->toArray($other);
        $callable = $this->callable;

        foreach ($other as $elem) {
            if ($callable($elem)) {
                return true;
            }
        }

        return false;
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
            'not at least one element of %s passes the test of the provided %s',
            $this->exporter->export($other),
            $this->toString()
        );
    }

    /**
     * @param array|\Traversable $other
     *
     * @return array
     */
    private function toArray($other)
    {
        if (\is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array) $other;
    }
}
