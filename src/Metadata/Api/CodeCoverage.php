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
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversClassesThatExtendClass;
use PHPUnit\Metadata\CoversClassesThatImplementInterface;
use PHPUnit\Metadata\CoversDirectory;
use PHPUnit\Metadata\CoversDirectoryRecursively;
use PHPUnit\Metadata\CoversFile;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\CoversMethod;
use PHPUnit\Metadata\CoversNamespace;
use PHPUnit\Metadata\CoversTrait;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\UsesClass;
use PHPUnit\Metadata\UsesClassesThatExtendClass;
use PHPUnit\Metadata\UsesClassesThatImplementInterface;
use PHPUnit\Metadata\UsesDirectory;
use PHPUnit\Metadata\UsesDirectoryRecursively;
use PHPUnit\Metadata\UsesFile;
use PHPUnit\Metadata\UsesFunction;
use PHPUnit\Metadata\UsesMethod;
use PHPUnit\Metadata\UsesNamespace;
use PHPUnit\Metadata\UsesTrait;
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
    public function coversTargets(string $className, string $methodName): TargetCollection
    {
        return $this->coversTargetsFor(Registry::parser()->forClassAndMethod($className, $methodName));
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function usesTargets(string $className, string $methodName): TargetCollection
    {
        return $this->usesTargetsFor(Registry::parser()->forClassAndMethod($className, $methodName));
    }

    public function shouldCodeCoverageBeCollectedFor(TestCase $test): bool
    {
        if (Registry::parser()->forClassAndMethod($test::class, $test->name())->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * @param class-string $className
     */
    public function coversNothingContradictsCoversOrUses(string $className): bool
    {
        $classLevel = Registry::parser()->forClass($className);

        if ($classLevel->isCoversNothing()->isEmpty()) {
            return false;
        }

        if ($this->coversTargetsFor($classLevel)->isNotEmpty() || $this->usesTargetsFor($classLevel)->isNotEmpty()) {
            return true;
        }

        return false;
    }

    private function coversTargetsFor(MetadataCollection $metadataCollection): TargetCollection
    {
        $targets = [];

        foreach ($metadataCollection as $metadata) {
            if ($metadata->isCoversNamespace()) {
                assert($metadata instanceof CoversNamespace);

                $targets[] = Target::forNamespace($metadata->namespace());
            }

            if ($metadata->isCoversClass()) {
                assert($metadata instanceof CoversClass);

                $targets[] = Target::forClass($metadata->className());
            }

            if ($metadata->isCoversClassesThatExtendClass()) {
                assert($metadata instanceof CoversClassesThatExtendClass);

                $targets[] = Target::forClassesThatExtendClass($metadata->className());
            }

            if ($metadata->isCoversClassesThatImplementInterface()) {
                assert($metadata instanceof CoversClassesThatImplementInterface);

                $targets[] = Target::forClassesThatImplementInterface($metadata->interfaceName());
            }

            if ($metadata->isCoversMethod()) {
                assert($metadata instanceof CoversMethod);

                $targets[] = Target::forMethod($metadata->className(), $metadata->methodName());
            }

            if ($metadata->isCoversFunction()) {
                assert($metadata instanceof CoversFunction);

                $targets[] = Target::forFunction($metadata->functionName());
            }

            if ($metadata->isCoversTrait()) {
                assert($metadata instanceof CoversTrait);

                $targets[] = Target::forTrait($metadata->traitName());
            }

            if ($metadata->isCoversFile()) {
                assert($metadata instanceof CoversFile);

                $targets[] = Target::forFile($metadata->path());
            }

            if ($metadata->isCoversDirectory()) {
                assert($metadata instanceof CoversDirectory);

                $targets[] = Target::forDirectory($metadata->directory());
            }

            if ($metadata->isCoversDirectoryRecursively()) {
                assert($metadata instanceof CoversDirectoryRecursively);

                $targets[] = Target::forDirectoryRecursively($metadata->directory());
            }
        }

        return TargetCollection::fromArray($targets);
    }

    private function usesTargetsFor(MetadataCollection $metadataCollection): TargetCollection
    {
        $targets = [];

        foreach ($metadataCollection as $metadata) {
            if ($metadata->isUsesNamespace()) {
                assert($metadata instanceof UsesNamespace);

                $targets[] = Target::forNamespace($metadata->namespace());
            }

            if ($metadata->isUsesClass()) {
                assert($metadata instanceof UsesClass);

                $targets[] = Target::forClass($metadata->className());
            }

            if ($metadata->isUsesClassesThatExtendClass()) {
                assert($metadata instanceof UsesClassesThatExtendClass);

                $targets[] = Target::forClassesThatExtendClass($metadata->className());
            }

            if ($metadata->isUsesClassesThatImplementInterface()) {
                assert($metadata instanceof UsesClassesThatImplementInterface);

                $targets[] = Target::forClassesThatImplementInterface($metadata->interfaceName());
            }

            if ($metadata->isUsesMethod()) {
                assert($metadata instanceof UsesMethod);

                $targets[] = Target::forMethod($metadata->className(), $metadata->methodName());
            }

            if ($metadata->isUsesFunction()) {
                assert($metadata instanceof UsesFunction);

                $targets[] = Target::forFunction($metadata->functionName());
            }

            if ($metadata->isUsesTrait()) {
                assert($metadata instanceof UsesTrait);

                $targets[] = Target::forTrait($metadata->traitName());
            }

            if ($metadata->isUsesFile()) {
                assert($metadata instanceof UsesFile);

                $targets[] = Target::forFile($metadata->path());
            }

            if ($metadata->isUsesDirectory()) {
                assert($metadata instanceof UsesDirectory);

                $targets[] = Target::forDirectory($metadata->directory());
            }

            if ($metadata->isUsesDirectoryRecursively()) {
                assert($metadata instanceof UsesDirectoryRecursively);

                $targets[] = Target::forDirectoryRecursively($metadata->directory());
            }
        }

        return TargetCollection::fromArray($targets);
    }
}
