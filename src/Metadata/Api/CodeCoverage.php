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
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\CoversMethod;
use PHPUnit\Metadata\CoversTrait;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\UsesClass;
use PHPUnit\Metadata\UsesFunction;
use PHPUnit\Metadata\UsesMethod;
use PHPUnit\Metadata\UsesTrait;
use ReflectionClass;
use SebastianBergmann\CodeUnit\CodeUnitCollection;
use SebastianBergmann\CodeUnit\Exception as CodeUnitException;
use SebastianBergmann\CodeUnit\Mapper;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverage
{
    /**
     * @psalm-var array<class-string, non-empty-list<class-string>>
     */
    private array $withParents = [];

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
            if (!$metadata->isCoversClass() && !$metadata->isCoversTrait() && !$metadata->isCoversMethod() && !$metadata->isCoversFunction()) {
                continue;
            }

            assert($metadata instanceof CoversClass || $metadata instanceof CoversTrait || $metadata instanceof CoversMethod || $metadata instanceof CoversFunction);

            $codeUnits = $codeUnits->mergeWith($this->mapToCodeUnits($metadata));
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
            if (!$metadata->isUsesClass() && !$metadata->isUsesTrait() && !$metadata->isUsesMethod() && !$metadata->isUsesFunction()) {
                continue;
            }

            assert($metadata instanceof UsesClass || $metadata instanceof UsesTrait || $metadata instanceof UsesMethod || $metadata instanceof UsesFunction);

            $codeUnits = $codeUnits->mergeWith($this->mapToCodeUnits($metadata));
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
    private function mapToCodeUnits(CoversClass|CoversFunction|CoversMethod|CoversTrait|UsesClass|UsesFunction|UsesMethod|UsesTrait $metadata): CodeUnitCollection
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
     * @psalm-return non-empty-list<non-empty-string>
     *
     * @throws InvalidCoversTargetException
     */
    private function names(CoversClass|CoversFunction|CoversMethod|CoversTrait|UsesClass|UsesFunction|UsesMethod|UsesTrait $metadata): array
    {
        $name  = $metadata->asStringForCodeUnitMapper();
        $names = [$name];

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

            if ($metadata->isCoversClass() && trait_exists($names[0])) {
                EventFacade::emitter()->testRunnerTriggeredDeprecation(
                    sprintf(
                        'Targeting a trait such as %s with #[CoversClass] is deprecated, please refactor your test to use #[CoversTrait] instead.',
                        $names[0],
                    ),
                );
            }

            if ($metadata->isUsesClass() && trait_exists($names[0])) {
                EventFacade::emitter()->testRunnerTriggeredDeprecation(
                    sprintf(
                        'Targeting a trait such as %s with #[UsesClass] is deprecated, please refactor your test to use #[UsesTrait] instead.',
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
