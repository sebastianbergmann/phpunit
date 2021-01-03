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

use function strlen;
use function strpos;
use PHPUnit\Framework\InvalidArgumentException;

/**
 * Constraint that asserts that the string it is evaluated for begins with a
 * given prefix.
 */
final class StringStartsWith extends Constraint
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix)
    {
        if (strlen($prefix) === 0) {
            throw InvalidArgumentException::create(1, 'non-empty string');
        }

        $this->prefix = $prefix;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'starts with "' . $this->prefix . '"';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        return strpos((string) $other, $this->prefix) === 0;
    }
}
