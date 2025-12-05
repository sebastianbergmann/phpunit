<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Iterators;

use Nette;


/**
 * Smarter caching iterator.
 *
 * @property-read bool $first
 * @property-read bool $last
 * @property-read bool $empty
 * @property-read bool $odd
 * @property-read bool $even
 * @property-read int $counter
 * @property-read mixed $nextKey
 * @property-read mixed $nextValue
 */
class CachingIterator extends \CachingIterator implements \Countable
{
	use Nette\SmartObject;

	private int $counter = 0;


	public function __construct(iterable|\stdClass $iterable)
	{
		$iterable = $iterable instanceof \stdClass
			? new \ArrayIterator((array) $iterable)
			: Nette\Utils\Iterables::toIterator($iterable);
		parent::__construct($iterable, 0);
	}


	/**
	 * Is the current element the first one?
	 */
	public function isFirst(?int $gridWidth = null): bool
	{
		return $this->counter === 1 || ($gridWidth && $this->counter !== 0 && (($this->counter - 1) % $gridWidth) === 0);
	}


	/**
	 * Is the current element the last one?
	 */
	public function isLast(?int $gridWidth = null): bool
	{
		return !$this->hasNext() || ($gridWidth && ($this->counter % $gridWidth) === 0);
	}


	/**
	 * Is the iterator empty?
	 */
	public function isEmpty(): bool
	{
		return $this->counter === 0;
	}


	/**
	 * Is the counter odd?
	 */
	public function isOdd(): bool
	{
		return $this->counter % 2 === 1;
	}


	/**
	 * Is the counter even?
	 */
	public function isEven(): bool
	{
		return $this->counter % 2 === 0;
	}


	/**
	 * Returns the counter.
	 */
	public function getCounter(): int
	{
		return $this->counter;
	}


	/**
	 * Returns the count of elements.
	 */
	public function count(): int
	{
		$inner = $this->getInnerIterator();
		if ($inner instanceof \Countable) {
			return $inner->count();

		} else {
			throw new Nette\NotSupportedException('Iterator is not countable.');
		}
	}


	/**
	 * Forwards to the next element.
	 */
	public function next(): void
	{
		parent::next();
		if (parent::valid()) {
			$this->counter++;
		}
	}


	/**
	 * Rewinds the Iterator.
	 */
	public function rewind(): void
	{
		parent::rewind();
		$this->counter = parent::valid() ? 1 : 0;
	}


	/**
	 * Returns the next key.
	 */
	public function getNextKey(): mixed
	{
		return $this->getInnerIterator()->key();
	}


	/**
	 * Returns the next element.
	 */
	public function getNextValue(): mixed
	{
		return $this->getInnerIterator()->current();
	}
}
