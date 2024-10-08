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
use function class_exists;
use function count;
use function interface_exists;
use function sprintf;
use function trait_exists;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\CoversMethod;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\UsesClass;
use PHPUnit\Metadata\UsesFunction;
use PHPUnit\Metadata\UsesMethod;
use ReflectionClass;
use SebastianBergmann\CodeUnit\CodeUnitCollection;
use SebastianBergmann\CodeUnit\Exception as CodeUnitException;
use SebastianBergmann\CodeUnit\Mapper;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverage
{
    /**
     * @var array<class-string, non-empty-list<class-string>>
     */
    private array $withParents = [];

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws CodeCoverageException
     *
     * @return array<string,list<int>>|false
     */
    public function linesToBeCovered(string $className, string $methodName): array|false
    {
        if (!$this->shouldCodeCoverageBeCollectedFor($className, $methodName)) {
            return false;
        }

        $codeUnits = CodeUnitCollection::fromList();
        $mapper    = new Mapper;

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if (!$metadata->isCoversClass() && !$metadata->isCoversMethod() && !$metadata->isCoversFunction()) {
                continue;
            }

            /** @phpstan-ignore booleanOr.alwaysTrue */
            assert($metadata instanceof CoversClass || $metadata instanceof CoversMethod || $metadata instanceof CoversFunction);

            if ($metadata->isCoversClass() || $metadata->isCoversMethod() || $metadata->isCoversFunction()) {
                $codeUnits = $codeUnits->mergeWith($this->mapToCodeUnits($metadata));
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @throws CodeCoverageException
     *
     * @return array<string,list<int>>
     */
    public function linesToBeUsed(string $className, string $methodName): array
    {
        $codeUnits = CodeUnitCollection::fromList();
        $mapper    = new Mapper;

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if (!$metadata->isUsesClass() && !$metadata->isUsesMethod() && !$metadata->isUsesFunction()) {
                continue;
            }

            /** @phpstan-ignore booleanOr.alwaysTrue */
            assert($metadata instanceof UsesClass || $metadata instanceof UsesMethod || $metadata instanceof UsesFunction);

            if ($metadata->isUsesClass() || $metadata->isUsesMethod() || $metadata->isUsesFunction()) {
                $codeUnits = $codeUnits->mergeWith($this->mapToCodeUnits($metadata));
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
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

        if ($metadataForMethod->isCoversClass()->isNotEmpty() ||
            $metadataForMethod->isCoversFunction()->isNotEmpty()) {
            return true;
        }

        if ($metadataForClass->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidCoversTargetException
     */
    private function mapToCodeUnits(CoversClass|CoversFunction|CoversMethod|UsesClass|UsesFunction|UsesMethod $metadata): CodeUnitCollection
    {
        $mapper = new Mapper;
        $names  = $this->names($metadata);

        try {
            if (count($names) === 1) {
                return $mapper->stringToCodeUnits($names[0]);
            }

            $codeUnits = CodeUnitCollection::fromList();

            foreach ($names as $name) {
                $codeUnits = $codeUnits->mergeWith(
                    $mapper->stringToCodeUnits($name),
                );
            }

            return $codeUnits;
        } catch (CodeUnitException $e) {
            throw new InvalidCoversTargetException(
                sprintf(
                    '%s is not a valid target for code coverage',
                    $metadata->asStringForCodeUnitMapper(),
                ),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws InvalidCoversTargetException
     *
     * @return non-empty-list<non-empty-string>
     */
    private function names(CoversClass|CoversFunction|CoversMethod|UsesClass|UsesFunction|UsesMethod $metadata): array
    {
        $name  = $metadata->asStringForCodeUnitMapper();
        $names = [$name];

        if (($metadata->isCoversMethod() || $metadata->isUsesMethod()) &&
            trait_exists($metadata->className())) {
            throw new InvalidCoversTargetException(
                sprintf(
                    'Trait %s is not a valid target for code coverage',
                    $metadata->className(),
                ),
            );
        }

        if ($metadata->isCoversClass() || $metadata->isUsesClass()) {
            if (isset($this->withParents[$name])) {
                return $this->withParents[$name];
            }

            if (interface_exists($name)) {
                throw new InvalidCoversTargetException(
                    sprintf(
                        'Interface "%s" is not a valid target for code coverage',
                        $name,
                    ),
                );
            }

            if (!(class_exists($name) || trait_exists($name))) {
                throw new InvalidCoversTargetException(
                    sprintf(
                        '"%s" is not a valid target for code coverage',
                        $name,
                    ),
                );
            }

            assert(class_exists($names[0]) || trait_exists($names[0]));

            if (trait_exists($names[0]) &&
                ($metadata->isCoversClass() || $metadata->isUsesClass())) {
                throw new InvalidCoversTargetException(
                    sprintf(
                        'Trait %s is not a valid target for code coverage',
                        $names[0],
                    ),
                );
            }

            $reflector = new ReflectionClass($name);

            while ($reflector = $reflector->getParentClass()) {
                if (!$reflector->isUserDefined()) {
                    break;
                }

                $names[] = $reflector->getName();
            }

            $this->withParents[$name] = $names;
        }

        return $names;
    }
}
