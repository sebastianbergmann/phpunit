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

use SplObjectStorage;

/**
 * Constraint that asserts that the Traversable it is applied to contains
 * a given value.
 */
class TraversableContains extends Constraint
{
    /**
     * @var bool
     */
    private $checkForObjectIdentity;

    /**
     * @var bool
     */
    private $checkForNonObjectIdentity;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     * @param bool  $checkForObjectIdentity
     * @param bool  $checkForNonObjectIdentity
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($value, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false)
    {
        parent::__construct();

        $this->checkForObjectIdentity    = $checkForObjectIdentity;
        $this->checkForNonObjectIdentity = $checkForNonObjectIdentity;
        $this->value                     = $value;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return string
     */
    public function toString(): string
    {
        if (\is_string($this->value) && \strpos($this->value, "\n") !== false) {
            return 'contains "' . $this->value . '"';
        }

        return 'contains ' . $this->exporter->export($this->value);
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
        if ($other instanceof SplObjectStorage) {
            return $other->contains($this->value);
        }

        if (\is_object($this->value)) {
            foreach ($other as $element) {
                if ($this->checkForObjectIdentity && $element === $this->value) {
                    return true;
                }

                if (!$this->checkForObjectIdentity && $element == $this->value) {
                    return true;
                }
            }
        } else {
            foreach ($other as $element) {
                if ($this->checkForNonObjectIdentity && $element === $this->value) {
                    return true;
                }

                if (!$this->checkForNonObjectIdentity && $element == $this->value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s %s',
            \is_array($other) ? 'an array' : 'a traversable',
            $this->toString()
        );
    }
}
