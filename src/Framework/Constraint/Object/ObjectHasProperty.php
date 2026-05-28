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

use function gettype;
use function is_object;
use function is_scalar;
use function sprintf;
use ReflectionObject;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ObjectHasProperty extends Constraint
{
    private readonly string $propertyName;

    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return sprintf(
            'has property "%s"',
            $this->propertyName,
        );
    }

    /**
     * Returns the negated description when this constraint is wrapped in a
     * LogicalNot operator. The guard ensures that LogicalAnd, LogicalOr, and
     * LogicalXor keep using the affirmative toString().
     */
    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        if (!$operator instanceof LogicalNot) {
            return '';
        }

        return sprintf(
            'does not have property "%s"',
            $this->propertyName,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches(mixed $other): bool
    {
        if (!is_object($other)) {
            return false;
        }

        return new ReflectionObject($other)->hasProperty($this->propertyName);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription(mixed $other): string
    {
        return $this->describe($other, $this->toString());
    }

    protected function failureDescriptionInContext(Operator $operator, mixed $role, mixed $other): string
    {
        // @codeCoverageIgnoreStart
        if (!$operator instanceof LogicalNot) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        return $this->describe($other, $this->toStringInContext($operator, $role));
    }

    private function describe(mixed $other, string $propertyDescription): string
    {
        if (is_object($other)) {
            return sprintf(
                'object of class "%s" %s',
                $other::class,
                $propertyDescription,
            );
        }

        if (is_scalar($other)) {
            $value = (string) $other;
        } else {
            $value = '';
        }

        return sprintf(
            '"%s" (%s) %s',
            $value,
            gettype($other),
            $propertyDescription,
        );
    }
}
