<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class FilterDirectoryCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var FilterDirectory[]
     */
    private $directories;

    /**
     * @param FilterDirectory[] $directories
     */
    public static function fromArray(array $directories): self
    {
        return new self(...$directories);
    }

    private function __construct(FilterDirectory ...$directories)
    {
        $this->directories = $directories;
    }

    /**
     * @return FilterDirectory[]
     */
    public function asArray(): array
    {
        return $this->directories;
    }

    public function count(): int
    {
        return \count($this->directories);
    }

    public function getIterator(): FilterDirectoryCollectionIterator
    {
        return new FilterDirectoryCollectionIterator($this);
    }
}
