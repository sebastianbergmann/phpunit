<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use PHPUnit\Metadata\MetadataCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ParserChain implements Parser
{
    private readonly Parser $attributeReader;
    private readonly Parser $annotationReader;

    public function __construct(Parser $attributeReader, Parser $annotationReader)
    {
        $this->attributeReader  = $attributeReader;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        $metadata = $this->attributeReader->forClass($className);

        if (!$metadata->isEmpty()) {
            return $metadata;
        }

        return $this->annotationReader->forClass($className);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $metadata = $this->attributeReader->forMethod($className, $methodName);

        if (!$metadata->isEmpty()) {
            return $metadata;
        }

        return $this->annotationReader->forMethod($className, $methodName);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClassAndMethod(string $className, string $methodName): MetadataCollection
    {
        return $this->forClass($className)->mergeWith(
            $this->forMethod($className, $methodName)
        );
    }
}
