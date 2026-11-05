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
 * Thrown when a state machine cannot be run, for instance because it has no
 * rule or a pool from which one of its rules takes a value is not produced by
 * any of them.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvalidStateMachineException extends RuntimeException implements Exception
{
    /**
     * @param class-string $className
     */
    public static function because(string $className, string $reason): self
    {
        return new self(
            sprintf(
                'State machine %s cannot be run: %s',
                $className,
                $reason,
            ),
        );
    }
}
