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

use ArrayAccess;

/**
 * Constraint which asserts that an array contains a given key value pair.
 *
 * An array key and value are passed in a constructor
 */
class ArrayHasKeyValuePair extends Constraint
{
    /**
     * @var int|string
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param int|string $key
     * @param mixed      $value
     */
    public function __construct($key, $value)
    {
        parent::__construct();

        $this->key   = $key;
        $this->value = $value;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return \sprintf(
          'has the %s => %s key value pair',
          $this->exporter->export($this->key),
          $this->exporter->export($this->value)
        );
    }

    protected function matches($other): bool
    {
        if (\is_array($other)) {
            return \array_key_exists($this->key, $other) && $other[$this->key] === $this->value;
        }

        if ($other instanceof ArrayAccess) {
            return $other->offsetExists($this->key) && $other[$this->key] === $this->value;
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
     * @return string
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }
}
