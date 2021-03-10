<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use function assert;
use function count;
use function in_array;
use function interface_exists;
use function sprintf;
use function strpos;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\IncompleteTestCase;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\SkippedTestCase;
use PHPUnit\Framework\WarningTestCase;
use SebastianBergmann\CodeUnit\CodeUnitCollection;
use SebastianBergmann\CodeUnit\InvalidCodeUnitException;
use SebastianBergmann\CodeUnit\Mapper;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverageFacade
{
    /**
     * @throws CodeCoverageException
     *
     * @return array|bool
     * @psalm-param class-string $className
     */
    public function linesToBeCovered(string $className, string $methodName)
    {
        if (!$this->shouldCodeCoverageBeCollectedFor($className, $methodName)) {
            return false;
        }

        $metadataForClass = Registry::parser()->forClass($className);
        $classShortcut    = null;

        if ($metadataForClass->isCoversDefaultClass()->isNotEmpty()) {
            if (count($metadataForClass->isCoversDefaultClass()) > 1) {
                throw new CodeCoverageException(
                    sprintf(
                        'More than one @coversDefaultClass annotation for class or interface "%s"',
                        $className
                    )
                );
            }

            $classShortcut = $metadataForClass->isCoversDefaultClass()->asArray()[0]->className();
        }

        $codeUnits = CodeUnitCollection::fromArray([]);
        $mapper    = new Mapper;

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isCoversClass() || $metadata->isCoversMethod() || $metadata->isCoversFunction()) {
                assert($metadata instanceof CoversClass || $metadata instanceof CoversMethod || $metadata instanceof CoversFunction);

                try {
                    $codeUnits = $codeUnits->mergeWith(
                        $mapper->stringToCodeUnits($metadata->asStringForCodeUnitMapper())
                    );
                } catch (InvalidCodeUnitException $e) {
                    if ($metadata->isCoversClass()) {
                        $type = 'Class';
                    } elseif ($metadata->isCoversMethod()) {
                        $type = 'Method';
                    } else {
                        $type = 'Function';
                    }

                    throw new InvalidCoversTargetException(
                        sprintf(
                            '%s "%s" is not a valid target for code coverage',
                            $type,
                            $metadata->asStringForCodeUnitMapper()
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            } elseif ($metadata->isCovers()) {
                assert($metadata instanceof Covers);

                $target = $metadata->target();

                if (interface_exists($target)) {
                    throw new InvalidCoversTargetException(
                        sprintf(
                            'Trying to @cover interface "%s".',
                            $target
                        )
                    );
                }

                if ($classShortcut !== null && strpos($target, '::') === 0) {
                    $target = $classShortcut . $target;
                }

                try {
                    $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($target));
                } catch (InvalidCodeUnitException $e) {
                    throw new InvalidCoversTargetException(
                        sprintf(
                            '"@covers %s" is invalid',
                            $target
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * Returns lines of code specified with the @uses annotation.
     *
     * @throws CodeCoverageException
     * @psalm-param class-string $className
     */
    public function linesToBeUsed(string $className, string $methodName): array
    {
        $metadataForClass = Registry::parser()->forClass($className);
        $classShortcut    = null;

        if ($metadataForClass->isUsesDefaultClass()->isNotEmpty()) {
            if (count($metadataForClass->isUsesDefaultClass()) > 1) {
                throw new CodeCoverageException(
                    sprintf(
                        'More than one @usesDefaultClass annotation for class or interface "%s"',
                        $className
                    )
                );
            }

            $classShortcut = $metadataForClass->isUsesDefaultClass()->asArray()[0]->className();
        }

        $codeUnits = CodeUnitCollection::fromArray([]);
        $mapper    = new Mapper;

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isUsesClass() || $metadata->isUsesMethod() || $metadata->isUsesFunction()) {
                assert($metadata instanceof UsesClass || $metadata instanceof UsesMethod || $metadata instanceof UsesFunction);

                try {
                    $codeUnits = $codeUnits->mergeWith(
                        $mapper->stringToCodeUnits($metadata->asStringForCodeUnitMapper())
                    );
                } catch (InvalidCodeUnitException $e) {
                    if ($metadata->isUsesClass()) {
                        $type = 'Class';
                    } elseif ($metadata->isUsesMethod()) {
                        $type = 'Method';
                    } else {
                        $type = 'Function';
                    }

                    throw new InvalidCoversTargetException(
                        sprintf(
                            '%s "%s" is not a valid target for code coverage',
                            $type,
                            $metadata->asStringForCodeUnitMapper()
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            } elseif ($metadata->isUses()) {
                assert($metadata instanceof Uses);

                $target = $metadata->target();

                if ($classShortcut !== null && strpos($target, '::') === 0) {
                    $target = $classShortcut . $target;
                }

                try {
                    $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($target));
                } catch (InvalidCodeUnitException $e) {
                    throw new InvalidCoversTargetException(
                        sprintf(
                            '"@uses %s" is invalid',
                            $target
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * @psalm-param class-string $className
     */
    public function shouldCodeCoverageBeCollectedFor(string $className, string $methodName): bool
    {
        if (in_array($className, [ErrorTestCase::class, IncompleteTestCase::class, SkippedTestCase::class, WarningTestCase::class], true)) {
            return false;
        }

        $metadataForClass  = Registry::parser()->forClass($className);
        $metadataForMethod = Registry::parser()->forMethod($className, $methodName);

        // If there is no @covers annotation but a @coversNothing annotation on
        // the test method then code coverage data does not need to be collected
        if ($metadataForMethod->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        // If there is at least one @covers annotation then
        // code coverage data needs to be collected
        if ($metadataForMethod->isCovers()->isNotEmpty() ||
            $metadataForMethod->isCoversClass()->isNotEmpty() ||
            $metadataForMethod->isCoversMethod()->isNotEmpty() ||
            $metadataForMethod->isCoversFunction()->isNotEmpty()) {
            return true;
        }

        // If there is no @covers annotation but a @coversNothing annotation
        // then code coverage data does not need to be collected
        if ($metadataForClass->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        // If there is no @coversNothing annotation then
        // code coverage data may be collected
        return true;
    }
}
