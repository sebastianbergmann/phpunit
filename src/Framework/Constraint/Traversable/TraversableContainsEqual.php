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

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
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
        if ($other instanceof SplObjectStorage) {
            return $other->offsetExists($this->value());
        }

        $comparatorFactory = ComparatorFactory::getInstance();

        foreach ($other as $element) {
            try {
                $comparator = $comparatorFactory->getComparatorFor(
                    $this->value(),
                    $element,
                );

                $comparator->assertEquals(
                    $this->value(),
                    $element,
                );

                return true;
            } catch (ComparisonFailure) {
                continue;
            }
        }

        return false;
    }
}
