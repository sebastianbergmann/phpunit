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

use function array_filter;
use function array_merge;
use function count;
use Countable;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<int, Metadata>
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class MetadataCollection implements Countable, IteratorAggregate
{
    /**
     * @var list<Metadata>
     */
    private array $metadata;

    /**
     * @param list<Metadata> $metadata
     */
    public static function fromArray(array $metadata): self
    {
        return new self(...$metadata);
    }

    private function __construct(Metadata ...$metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return list<Metadata>
     */
    public function asArray(): array
    {
        return $this->metadata;
    }

    public function count(): int
    {
        return count($this->metadata);
    }

    /**
     * @phpstan-assert-if-true 0 $this->count()
     * @phpstan-assert-if-true array{} $this->asArray()
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @phpstan-assert-if-true positive-int $this->count()
     * @phpstan-assert-if-true non-empty-list<Metadata> $this->asArray()
     */
    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    public function getIterator(): MetadataCollectionIterator
    {
        return new MetadataCollectionIterator($this);
    }

    public function mergeWith(self $other): self
    {
        return new self(
            ...array_merge(
                $this->asArray(),
                $other->asArray(),
            ),
        );
    }

    public function isClassLevel(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isClassLevel(),
            ),
        );
    }

    public function isMethodLevel(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isMethodLevel(),
            ),
        );
    }

    public function isAfter(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isAfter(),
            ),
        );
    }

    public function isAfterClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isAfterClass(),
            ),
        );
    }

    public function isAllowMockObjectsWithoutExpectations(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isAllowMockObjectsWithoutExpectations(),
            ),
        );
    }

    public function isBackupGlobals(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isBackupGlobals(),
            ),
        );
    }

    public function isBackupStaticProperties(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isBackupStaticProperties(),
            ),
        );
    }

    public function isBeforeClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isBeforeClass(),
            ),
        );
    }

    public function isBefore(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isBefore(),
            ),
        );
    }

    public function isCoversNamespace(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversNamespace(),
            ),
        );
    }

    public function isCoversClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversClass(),
            ),
        );
    }

    public function isCoversClassesThatExtendClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversClassesThatExtendClass(),
            ),
        );
    }

    public function isCoversClassesThatImplementInterface(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversClassesThatImplementInterface(),
            ),
        );
    }

    public function isCoversTrait(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversTrait(),
            ),
        );
    }

    public function isCoversFunction(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversFunction(),
            ),
        );
    }

    public function isCoversMethod(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversMethod(),
            ),
        );
    }

    public function isExcludeGlobalVariableFromBackup(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isExcludeGlobalVariableFromBackup(),
            ),
        );
    }

    public function isExcludeStaticPropertyFromBackup(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isExcludeStaticPropertyFromBackup(),
            ),
        );
    }

    public function isCoversNothing(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isCoversNothing(),
            ),
        );
    }

    public function isDataProvider(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isDataProvider(),
            ),
        );
    }

    public function isDepends(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isDependsOnClass() || $metadata->isDependsOnMethod(),
            ),
        );
    }

    public function isDependsOnClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isDependsOnClass(),
            ),
        );
    }

    public function isDependsOnMethod(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isDependsOnMethod(),
            ),
        );
    }

    public function isDisableReturnValueGenerationForTestDoubles(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isDisableReturnValueGenerationForTestDoubles(),
            ),
        );
    }

    public function isDoesNotPerformAssertions(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isDoesNotPerformAssertions(),
            ),
        );
    }

    public function isGroup(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isGroup(),
            ),
        );
    }

    public function isIgnoreDeprecations(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isIgnoreDeprecations(),
            ),
        );
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function isIgnorePhpunitDeprecations(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isIgnorePhpunitDeprecations(),
            ),
        );
    }

    public function isIgnorePhpunitWarnings(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isIgnorePhpunitWarnings(),
            ),
        );
    }

    public function isRunClassInSeparateProcess(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRunClassInSeparateProcess(),
            ),
        );
    }

    public function isRunInSeparateProcess(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRunInSeparateProcess(),
            ),
        );
    }

    public function isRunTestsInSeparateProcesses(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRunTestsInSeparateProcesses(),
            ),
        );
    }

    public function isTest(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isTest(),
            ),
        );
    }

    public function isPreCondition(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isPreCondition(),
            ),
        );
    }

    public function isPostCondition(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isPostCondition(),
            ),
        );
    }

    public function isPreserveGlobalState(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isPreserveGlobalState(),
            ),
        );
    }

    public function isRequiresMethod(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresMethod(),
            ),
        );
    }

    public function isRequiresFunction(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresFunction(),
            ),
        );
    }

    public function isRequiresOperatingSystem(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresOperatingSystem(),
            ),
        );
    }

    public function isRequiresOperatingSystemFamily(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresOperatingSystemFamily(),
            ),
        );
    }

    public function isRequiresPhp(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresPhp(),
            ),
        );
    }

    public function isRequiresPhpExtension(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresPhpExtension(),
            ),
        );
    }

    public function isRequiresPhpunit(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresPhpunit(),
            ),
        );
    }

    public function isRequiresPhpunitExtension(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresPhpunitExtension(),
            ),
        );
    }

    public function isRequiresEnvironmentVariable(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresEnvironmentVariable(),
            ),
        );
    }

    public function isWithEnvironmentVariable(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isWithEnvironmentVariable(),
            ),
        );
    }

    public function isRequiresSetting(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isRequiresSetting(),
            ),
        );
    }

    public function isTestDox(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isTestDox(),
            ),
        );
    }

    public function isTestDoxFormatter(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isTestDoxFormatter(),
            ),
        );
    }

    public function isTestWith(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isTestWith(),
            ),
        );
    }

    public function isUsesNamespace(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesNamespace(),
            ),
        );
    }

    public function isUsesClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesClass(),
            ),
        );
    }

    public function isUsesClassesThatExtendClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesClassesThatExtendClass(),
            ),
        );
    }

    public function isUsesClassesThatImplementInterface(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesClassesThatImplementInterface(),
            ),
        );
    }

    public function isUsesTrait(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesTrait(),
            ),
        );
    }

    public function isUsesFunction(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesFunction(),
            ),
        );
    }

    public function isUsesMethod(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isUsesMethod(),
            ),
        );
    }

    public function isWithoutErrorHandler(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static fn (Metadata $metadata): bool => $metadata->isWithoutErrorHandler(),
            ),
        );
    }
}
