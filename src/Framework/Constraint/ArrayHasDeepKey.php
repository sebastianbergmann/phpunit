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

use ArrayAccess;
use ArrayObject;

/**
 * Constraint that asserts that the array it is evaluated for has a given key in deep.
 *
 * Uses json_encode() to encode nested array and strpos() to check if the key is found in the input array, if
 * not found the evaluation fails.
 *
 * The array key is passed in the constructor.
 */
final class ArrayHasDeepKey extends Constraint
{
    /**
     * @var int|string
     */
    private $key;

    /**
     * @param int|string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        return 'has the deep key ' . $this->exporter()->export($this->key);
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        if (\is_array($other)) {
            return \strpos(\json_encode($other, \JSON_FORCE_OBJECT), "\"$this->key\":") ? true : false;
        }

        if ($other instanceof ArrayAccess) {
            $other = new ArrayObject($other);

            return \strpos(\json_encode($other->getArrayCopy(), \JSON_FORCE_OBJECT), "\"$this->key\":") ? true : false;
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }
}
