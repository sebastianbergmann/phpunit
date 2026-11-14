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
use InvalidArgumentException;

/**
 * Thrown when an object of a class that implements ValueGenerator but was
 * not created by PHPUnit is used as a generator.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ValueGeneratorNotSupportedException extends InvalidArgumentException implements Exception
{
    /**
     * @param class-string<ValueGenerator<mixed>> $className
     */
    public static function forClass(string $className): self
    {
        return new self(
            sprintf(
                'Class %s implements %s, which is not supported: generators have to be created and composed with the methods of %s',
                $className,
                ValueGenerator::class,
                Generators::class,
            ),
        );
    }
}
