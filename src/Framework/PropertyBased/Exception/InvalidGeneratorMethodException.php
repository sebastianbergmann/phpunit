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

use RuntimeException;

/**
 * Thrown when a generator cannot be created with the class or the method that
 * it is specified with. The message is the reason, which the caller reports
 * for the property or the state machine that specifies the generator.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvalidGeneratorMethodException extends RuntimeException implements Exception
{
    /**
     * @param non-empty-string $reason
     */
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
