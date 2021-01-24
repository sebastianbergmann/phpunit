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

use function array_filter;
use function array_merge;
use function count;
use Countable;
use IteratorAggregate;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class MetadataCollection implements Countable, IteratorAggregate
{
    /**
     * @var Metadata[]
     */
    private array $metadata;

    /**
     * @param Metadata[] $metadata
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
     * @return Metadata[]
     */
    public function asArray(): array
    {
        return $this->metadata;
    }

    public function count(): int
    {
        return count($this->metadata);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

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
                $other->asArray()
            )
        );
    }

    public function isAfter(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isAfter();
                }
            )
        );
    }

    public function isAfterClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isAfterClass();
                }
            )
        );
    }

    public function isBackupGlobals(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isBackupGlobals();
                }
            )
        );
    }

    public function isBackupStaticProperties(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isBackupStaticProperties();
                }
            )
        );
    }

    public function isBeforeClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isBeforeClass();
                }
            )
        );
    }

    public function isBefore(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isBefore();
                }
            )
        );
    }

    public function isCodeCoverageIgnore(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isCodeCoverageIgnore();
                }
            )
        );
    }

    public function isCoversClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isCoversClass();
                }
            )
        );
    }

    public function isCoversDefaultClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isCoversDefaultClass();
                }
            )
        );
    }

    public function isCoversMethod(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isCoversMethod();
                }
            )
        );
    }

    public function isCoversFunction(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isCoversFunction();
                }
            )
        );
    }

    public function isCoversNothing(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isCoversNothing();
                }
            )
        );
    }

    public function isDataProvider(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isDataProvider();
                }
            )
        );
    }

    public function isDepends(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isDepends();
                }
            )
        );
    }

    public function isDoesNotPerformAssertions(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isDoesNotPerformAssertions();
                }
            )
        );
    }

    public function isGroup(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isGroup();
                }
            )
        );
    }

    public function isRunTestsInSeparateProcesses(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRunTestsInSeparateProcesses();
                }
            )
        );
    }

    public function isRunInSeparateProcess(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRunInSeparateProcess();
                }
            )
        );
    }

    public function isTest(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isTest();
                }
            )
        );
    }

    public function isPreCondition(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isPreCondition();
                }
            )
        );
    }

    public function isPostCondition(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isPostCondition();
                }
            )
        );
    }

    public function isPreserveGlobalState(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isPreserveGlobalState();
                }
            )
        );
    }

    public function isRequiresFunction(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRequiresFunction();
                }
            )
        );
    }

    public function isRequiresOperatingSystem(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRequiresOperatingSystem();
                }
            )
        );
    }

    public function isRequiresOperatingSystemFamily(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRequiresOperatingSystemFamily();
                }
            )
        );
    }

    public function isRequiresPhp(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRequiresPhp();
                }
            )
        );
    }

    public function isRequiresPhpExtension(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRequiresPhpExtension();
                }
            )
        );
    }

    public function isRequiresPhpunit(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isRequiresPhpunit();
                }
            )
        );
    }

    public function isTestDox(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isTestDox();
                }
            )
        );
    }

    public function isTestWith(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isTestWith();
                }
            )
        );
    }

    public function isUsesClass(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isUsesClass();
                }
            )
        );
    }

    public function isUsesMethod(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isUsesMethod();
                }
            )
        );
    }

    public function isUsesFunction(): self
    {
        return new self(
            ...array_filter(
                $this->metadata,
                static function (Metadata $metadata): bool {
                    return $metadata->isUsesFunction();
                }
            )
        );
    }
}
