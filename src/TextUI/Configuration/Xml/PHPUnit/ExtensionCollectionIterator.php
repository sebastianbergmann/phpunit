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
use function iterator_count;
use Countable;
use Iterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExtensionCollectionIterator implements Countable, Iterator
{
    /**
     * @psalm-var list<Extension>
     */
    private array $extensions;
    private ?int $position = null;

    public function __construct(ExtensionCollection $extensions)
    {
        $this->extensions = $extensions->asArray();
    }

    public function count(): int
    {
        return iterator_count($this);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < count($this->extensions);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): Extension
    {
        return $this->extensions[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
