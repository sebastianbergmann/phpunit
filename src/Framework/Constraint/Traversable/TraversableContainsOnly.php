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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\NativeType;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TraversableContainsOnly extends Constraint
{
    private readonly Constraint $constraint;
    private readonly string $type;

    public static function forNativeType(NativeType $type): self
    {
        return new self(new IsType($type), $type->value);
    }

    /**
     * @param class-string $type
     */
    public static function forClassOrInterface(string $type): self
    {
        return new self(new IsInstanceOf($type), $type);
    }

    private function __construct(IsInstanceOf|IsType $constraint, string $type)
    {
        $this->constraint = $constraint;
        $this->type       = $type;
    }

    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws ExpectationFailedException
     */
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): bool
    {
        $success = true;

        foreach ($other as $item) {
            if (!$this->constraint->evaluate($item, '', true)) {
                $success = false;

                break;
            }
        }

        if (!$success && !$returnResult) {
            $this->fail($other, $description);
        }

        return $success;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'contains only values of type "' . $this->type . '"';
    }
}
