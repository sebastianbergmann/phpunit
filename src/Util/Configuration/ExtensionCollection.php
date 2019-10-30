<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Configuration;

/**
 * @psalm-immutable
 */
final class ExtensionCollection implements \IteratorAggregate
{
    /**
     * @var Extension[]
     */
    private $extensions;

    /**
     * @param Extension[] $items
     */
    public static function fromArray(array $items): self
    {
        return new self(...$items);
    }

    private function __construct(Extension ...$extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @return Extension[]
     */
    public function asArray(): array
    {
        return $this->extensions;
    }

    public function getIterator(): ExtensionCollectionIterator
    {
        return new ExtensionCollectionIterator($this);
    }
}
