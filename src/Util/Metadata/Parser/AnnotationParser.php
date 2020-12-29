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

use PHPUnit\Util\Metadata\Annotation\Registry as AnnotationRegistry;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class AnnotationParser implements Parser
{
    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        $result = [];

        foreach (AnnotationRegistry::getInstance()->forClassName($className)->symbolAnnotations() as $annotation) {
        }

        return MetadataCollection::fromArray($result);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $result = [];

        foreach (AnnotationRegistry::getInstance()->forMethod($className, $methodName)->symbolAnnotations() as $annotation) {
        }

        return MetadataCollection::fromArray($result);
    }
}
