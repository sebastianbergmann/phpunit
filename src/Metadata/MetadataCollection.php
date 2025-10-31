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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class MetadataCollection implements Countable, IteratorAggregate
{
    /**
     * @var list<Metadata>
     */
    private readonly array $metadata;

    /**
     * @var array<non-empty-string, self>
     */
    private array $cache = [];

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
        return $this->filter('isClassLevel');
    }

    public function isMethodLevel(): self
    {
        return $this->filter('isMethodLevel');
    }

    public function isAfter(): self
    {
        return $this->filter('isAfter');
    }

    public function isAfterClass(): self
    {
        return $this->filter('isAfterClass');
    }

    public function isBackupGlobals(): self
    {
        return $this->filter('isBackupGlobals');
    }

    public function isBackupStaticProperties(): self
    {
        return $this->filter('isBackupStaticProperties');
    }

    public function isBeforeClass(): self
    {
        return $this->filter('isBeforeClass');
    }

    public function isBefore(): self
    {
        return $this->filter('isBefore');
    }

    public function isCoversNamespace(): self
    {
        return $this->filter('isCoversNamespace');
    }

    public function isCoversClass(): self
    {
        return $this->filter('isCoversClass');
    }

    public function isCoversClassesThatExtendClass(): self
    {
        return $this->filter('isCoversClassesThatExtendClass');
    }

    public function isCoversClassesThatImplementInterface(): self
    {
        return $this->filter('isCoversClassesThatImplementInterface');
    }

    public function isCoversTrait(): self
    {
        return $this->filter('isCoversTrait');
    }

    public function isCoversFunction(): self
    {
        return $this->filter('isCoversFunction');
    }

    public function isCoversMethod(): self
    {
        return $this->filter('isCoversMethod');
    }

    public function isExcludeGlobalVariableFromBackup(): self
    {
        return $this->filter('isExcludeGlobalVariableFromBackup');
    }

    public function isExcludeStaticPropertyFromBackup(): self
    {
        return $this->filter('isExcludeStaticPropertyFromBackup');
    }

    public function isCoversNothing(): self
    {
        return $this->filter('isCoversNothing');
    }

    public function isDataProvider(): self
    {
        return $this->filter('isDataProvider');
    }

    public function isDepends(): self
    {
        return $this->isDependsOnClass()->mergeWith($this->isDependsOnMethod());
    }

    public function isDependsOnClass(): self
    {
        return $this->filter('isDependsOnClass');
    }

    public function isDependsOnMethod(): self
    {
        return $this->filter('isDependsOnMethod');
    }

    public function isDisableReturnValueGenerationForTestDoubles(): self
    {
        return $this->filter('isDisableReturnValueGenerationForTestDoubles');
    }

    public function isDoesNotPerformAssertions(): self
    {
        return $this->filter('isDoesNotPerformAssertions');
    }

    public function isGroup(): self
    {
        return $this->filter('isGroup');
    }

    public function isIgnoreDeprecations(): self
    {
        return $this->filter('isIgnoreDeprecations');
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function isIgnorePhpunitDeprecations(): self
    {
        return $this->filter('isIgnorePhpunitDeprecations');
    }

    public function isIgnorePhpunitWarnings(): self
    {
        return $this->filter('isIgnorePhpunitWarnings');
    }

    public function isRunInSeparateProcess(): self
    {
        return $this->filter('isRunInSeparateProcess');
    }

    public function isRunTestsInSeparateProcesses(): self
    {
        return $this->filter('isRunTestsInSeparateProcesses');
    }

    public function isTest(): self
    {
        return $this->filter('isTest');
    }

    public function isPreCondition(): self
    {
        return $this->filter('isPreCondition');
    }

    public function isPostCondition(): self
    {
        return $this->filter('isPostCondition');
    }

    public function isPreserveGlobalState(): self
    {
        return $this->filter('isPreserveGlobalState');
    }

    public function isRequiresMethod(): self
    {
        return $this->filter('isRequiresMethod');
    }

    public function isRequiresFunction(): self
    {
        return $this->filter('isRequiresFunction');
    }

    public function isRequiresOperatingSystem(): self
    {
        return $this->filter('isRequiresOperatingSystem');
    }

    public function isRequiresOperatingSystemFamily(): self
    {
        return $this->filter('isRequiresOperatingSystemFamily');
    }

    public function isRequiresPhp(): self
    {
        return $this->filter('isRequiresPhp');
    }

    public function isRequiresPhpExtension(): self
    {
        return $this->filter('isRequiresPhpExtension');
    }

    public function isRequiresPhpunit(): self
    {
        return $this->filter('isRequiresPhpunit');
    }

    public function isRequiresPhpunitExtension(): self
    {
        return $this->filter('isRequiresPhpunitExtension');
    }

    public function isRequiresEnvironmentVariable(): self
    {
        return $this->filter('isRequiresEnvironmentVariable');
    }

    public function isWithEnvironmentVariable(): self
    {
        return $this->filter('isWithEnvironmentVariable');
    }

    public function isRequiresSetting(): self
    {
        return $this->filter('isRequiresSetting');
    }

    public function isTestDox(): self
    {
        return $this->filter('isTestDox');
    }

    public function isTestDoxFormatter(): self
    {
        return $this->filter('isTestDoxFormatter');
    }

    public function isTestWith(): self
    {
        return $this->filter('isTestWith');
    }

    public function isUsesNamespace(): self
    {
        return $this->filter('isUsesNamespace');
    }

    public function isUsesClass(): self
    {
        return $this->filter('isUsesClass');
    }

    public function isUsesClassesThatExtendClass(): self
    {
        return $this->filter('isUsesClassesThatExtendClass');
    }

    public function isUsesClassesThatImplementInterface(): self
    {
        return $this->filter('isUsesClassesThatImplementInterface');
    }

    public function isUsesTrait(): self
    {
        return $this->filter('isUsesTrait');
    }

    public function isUsesFunction(): self
    {
        return $this->filter('isUsesFunction');
    }

    public function isUsesMethod(): self
    {
        return $this->filter('isUsesMethod');
    }

    public function isWithoutErrorHandler(): self
    {
        return $this->filter('isWithoutErrorHandler');
    }

    /**
     * @param non-empty-string $filter
     */
    private function filter(string $filter): self
    {
        if (!isset($this->cache[$filter])) {
            $this->cache[$filter] = new self(
                ...array_filter(
                    $this->metadata,
                    /** @phpstan-ignore method.dynamicName */
                    static fn (Metadata $metadata): bool => $metadata->{$filter}(),
                ),
            );
        }

        return $this->cache[$filter];
    }
}
