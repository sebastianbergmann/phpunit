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

use function is_object;
use function sprintf;
use PHPUnit\Framework\Exception;
use ReflectionClass;
use ReflectionException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4601
 */
class ClassHasAttribute extends Constraint
{
    private string $attributeName;

    public function __construct(string $attributeName)
    {
        $this->attributeName = $attributeName;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return sprintf(
            'has attribute "%s"',
            $this->attributeName
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        try {
            return (new ReflectionClass($other))->hasProperty($this->attributeName);
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            '%sclass "%s" %s',
            is_object($other) ? 'object of ' : '',
            is_object($other) ? $other::class : $other,
            $this->toString()
        );
    }

    protected function attributeName(): string
    {
        return $this->attributeName;
    }
}
