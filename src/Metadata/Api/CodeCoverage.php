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
use function interface_exists;
use function sprintf;
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
use ReflectionException;
use SebastianBergmann\CodeUnit\CodeUnitCollection;
use SebastianBergmann\CodeUnit\Exception as CodeUnitException;
use SebastianBergmann\CodeUnit\Mapper;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CodeCoverage
{
    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @psalm-return array<string,list<int>>|false
     *
     * @throws CodeCoverageException
     */
    public function linesToBeCovered(string $className, string $methodName): array|false
    {
        if (!$this->shouldCodeCoverageBeCollectedFor($className, $methodName)) {
            return false;
        }

        $codeUnits = CodeUnitCollection::fromList();
        $mapper    = new Mapper;

        foreach (Registry::parser()->forClass($className) as $metadata) {
            if (!$metadata->isCoversClass() && !$metadata->isCoversMethod() && !$metadata->isCoversFunction()) {
                continue;
            }

            assert($metadata instanceof CoversClass || $metadata instanceof CoversMethod || $metadata instanceof CoversFunction);

            if ($metadata->isCoversClass() || $metadata->isCoversMethod() || $metadata->isCoversFunction()) {
                $codeUnits = $codeUnits->mergeWith($this->mapToCodeUnits($metadata));
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * @psalm-param class-string $className
     *
     * @psalm-return array<string,list<int>>
     *
     * @throws CodeCoverageException
     */
    public function linesToBeUsed(string $className): array
    {
        $codeUnits = CodeUnitCollection::fromList();
        $mapper    = new Mapper;

        foreach (Registry::parser()->forClass($className) as $metadata) {
            if (!$metadata->isUsesClass() && !$metadata->isUsesMethod() && !$metadata->isUsesFunction()) {
                continue;
            }

            assert($metadata instanceof UsesClass || $metadata instanceof UsesMethod || $metadata instanceof UsesFunction);

            if ($metadata->isUsesClass() || $metadata->isUsesMethod() || $metadata->isUsesFunction()) {
                $codeUnits = $codeUnits->mergeWith($this->mapToCodeUnits($metadata));
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     */
    public function shouldCodeCoverageBeCollectedFor(string $className, string $methodName): bool
    {
        $metadataForClass  = Registry::parser()->forClass($className);
        $metadataForMethod = Registry::parser()->forMethod($className, $methodName);

        if ($metadataForClass->isCoversNothing()->isNotEmpty() ||
            $metadataForMethod->isCoversNothing()->isNotEmpty()) {
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
        $names  = [$metadata->asStringForCodeUnitMapper()];

        if ($metadata->isCoversClass() || $metadata->isUsesClass()) {
            try {
                $reflector = new ReflectionClass($metadata->asStringForCodeUnitMapper());
            } catch (ReflectionException $e) {
                throw new InvalidCoversTargetException(
                    sprintf(
                        'Class "%s" is not a valid target for code coverage',
                        $metadata->asStringForCodeUnitMapper(),
                    ),
                    $e->getCode(),
                    $e,
                );
            }

            while ($reflector = $reflector->getParentClass()) {
                if (!$reflector->isUserDefined()) {
                    break;
                }

                $names[] = $reflector->getName();
            }
        }

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
            if ($metadata->isCoversClass() || $metadata->isUsesClass()) {
                if (interface_exists($metadata->className())) {
                    $type = 'Interface';
                } else {
                    $type = 'Class';
                }
            } else {
                $type = 'Function';
            }

            throw new InvalidCoversTargetException(
                sprintf(
                    '%s "%s" is not a valid target for code coverage',
                    $type,
                    $metadata->asStringForCodeUnitMapper(),
                ),
                $e->getCode(),
                $e,
            );
        }
    }
}
