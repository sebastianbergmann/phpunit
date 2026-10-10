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
 * Reports a generator that refers to itself before it makes a choice, so that
 * generating a value with it would recurse infinitely.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GeneratorRefersToItselfException extends RuntimeException implements Exception
{
    /**
     * @param non-empty-string $what
     */
    public static function generating(string $what): self
    {
        return new self(
            sprintf(
                'Cannot generate %s: the generator refers to itself before it makes a choice, so it would recurse infinitely, for instance because the generator that is registered for a class with #[GeneratorFor] uses Generators::objects() for the same class',
                $what,
            ),
        );
    }
}
