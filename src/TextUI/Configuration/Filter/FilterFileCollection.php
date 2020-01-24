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
final class FilterFileCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var FilterFile[]
     */
    private $files;

    /**
     * @param FilterFile[] $files
     */
    public static function fromArray(array $files): self
    {
        return new self(...$files);
    }

    private function __construct(FilterFile ...$files)
    {
        $this->files = $files;
    }

    /**
     * @return FilterFile[]
     */
    public function asArray(): array
    {
        return $this->files;
    }

    public function count(): int
    {
        return \count($this->files);
    }

    public function getIterator(): FilterFileCollectionIterator
    {
        return new FilterFileCollectionIterator($this);
    }
}
