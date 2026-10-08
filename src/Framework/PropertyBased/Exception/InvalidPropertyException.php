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
 * Thrown when a property cannot be checked, for instance because no generator
 * can be determined for one of its parameters.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvalidPropertyException extends RuntimeException implements Exception
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function because(string $className, string $methodName, string $reason): self
    {
        return new self(
            sprintf(
                'Property %s::%s cannot be checked: %s',
                $className,
                $methodName,
                $reason,
            ),
        );
    }
}
