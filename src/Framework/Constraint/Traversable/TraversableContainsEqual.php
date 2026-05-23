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

use function is_iterable;
use function is_object;
use SebastianBergmann\Comparator\ComparisonFailure;
use SplObjectStorage;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TraversableContainsEqual extends TraversableContains
{
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        $value = $this->value();

        if ($other instanceof SplObjectStorage) {
            if (!is_object($value)) {
                return false;
            }

            return $other->offsetExists($value);
        }

        if (!is_iterable($other)) {
            return false;
        }

        foreach ($other as $element) {
            try {
                $this->assertEqualsUsingComparator($this->value(), $element);

                return true;
            } catch (ComparisonFailure) {
                continue;
            }
        }

        return false;
    }
}
