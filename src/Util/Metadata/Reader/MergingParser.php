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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MergingParser implements Reader
{
    /**
     * @var Reader[]
     */
    private $readers;

    public function __construct(Reader ...$readers)
    {
        $this->readers = $readers;
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        $metadata = MetadataCollection::fromArray([]);

        foreach ($this->readers as $reader) {
            $metadata = $metadata->mergeWith($reader->forClass($className));
        }

        return $metadata;
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $metadata = MetadataCollection::fromArray([]);

        foreach ($this->readers as $reader) {
            $metadata = $metadata->mergeWith($reader->forMethod($className, $methodName));
        }

        return $metadata;
    }
}
