<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class AnnotationReader implements Reader
{
    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        return MetadataCollection::fromArray([]);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        return MetadataCollection::fromArray([]);
    }
}
