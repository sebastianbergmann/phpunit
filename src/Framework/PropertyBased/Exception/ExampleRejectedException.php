<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function sprintf;
use RuntimeException;

/**
 * Thrown while the arguments for an example are generated when the example
 * has to be discarded, for instance because a filter could not be satisfied.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExampleRejectedException extends RuntimeException implements Exception
{
    /**
     * @param positive-int $numberOfAttempts
     */
    public static function filterNotSatisfied(int $numberOfAttempts): self
    {
        return new self(
            sprintf(
                'No value that satisfies the filter was generated in %d attempts',
                $numberOfAttempts,
            ),
        );
    }

    /**
     * @param positive-int $maximumNumberOfChoices
     */
    public static function tooManyChoices(int $maximumNumberOfChoices): self
    {
        return new self(
            sprintf(
                'Generating the example required more than %d choices',
                $maximumNumberOfChoices,
            ),
        );
    }

    /**
     * @param positive-int $numberOfAttempts
     */
    public static function tooManyDuplicateElements(int $numberOfAttempts): self
    {
        return new self(
            sprintf(
                'No new element for the collection was generated in %d attempts',
                $numberOfAttempts,
            ),
        );
    }
}
