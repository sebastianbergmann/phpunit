<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function assert;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\CoversMethod;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\UsesClass;
use PHPUnit\Metadata\UsesFunction;
use PHPUnit\Metadata\UsesMethod;
use SebastianBergmann\CodeCoverage\Test\Target\Target;
use SebastianBergmann\CodeCoverage\Test\Target\TargetCollection;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverage
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function coversTargets(string $className, string $methodName): false|TargetCollection
    {
        if (!$this->shouldCodeCoverageBeCollectedFor($className, $methodName)) {
            return false;
        }

        $targets = [];

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isCoversClass()) {
                assert($metadata instanceof CoversClass);

                $targets[] = Target::forClass($metadata->className());
            }

            if ($metadata->isCoversMethod()) {
                assert($metadata instanceof CoversMethod);

                $targets[] = Target::forMethod($metadata->className(), $metadata->methodName());
            }

            if ($metadata->isCoversFunction()) {
                assert($metadata instanceof CoversFunction);

                $targets[] = Target::forFunction($metadata->functionName());
            }
        }

        return TargetCollection::fromArray($targets);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function usesTargets(string $className, string $methodName): TargetCollection
    {
        $targets = [];

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isUsesClass()) {
                assert($metadata instanceof UsesClass);

                $targets[] = Target::forClass($metadata->className());
            }

            if ($metadata->isUsesMethod()) {
                assert($metadata instanceof UsesMethod);

                $targets[] = Target::forMethod($metadata->className(), $metadata->methodName());
            }

            if ($metadata->isUsesFunction()) {
                assert($metadata instanceof UsesFunction);

                $targets[] = Target::forFunction($metadata->functionName());
            }
        }

        return TargetCollection::fromArray($targets);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function shouldCodeCoverageBeCollectedFor(string $className, string $methodName): bool
    {
        $metadataForClass  = Registry::parser()->forClass($className);
        $metadataForMethod = Registry::parser()->forMethod($className, $methodName);

        if ($metadataForMethod->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        if ($metadataForClass->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        return true;
    }
}
