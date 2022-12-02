<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function count;
use Countable;
use IteratorAggregate;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 *
 * @template-implements IteratorAggregate<int, TestFile>
 */
final class TestFileCollection implements Countable, IteratorAggregate
{
    /**
     * @var TestFile[]
     */
    private $files;

    /**
     * @param TestFile[] $files
     */
    public static function fromArray(array $files): self
    {
        return new self(...$files);
    }

    private function __construct(TestFile ...$files)
    {
        $this->files = $files;
    }

    /**
     * @return TestFile[]
     */
    public function asArray(): array
    {
        return $this->files;
    }

    public function count(): int
    {
        return count($this->files);
    }

    public function getIterator(): TestFileCollectionIterator
    {
        return new TestFileCollectionIterator($this);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
