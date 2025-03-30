<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;


/**
 * Provides objects to work as array.
 * @template T
 * @implements \IteratorAggregate<array-key, T>
 * @implements \ArrayAccess<array-key, T>
 */
class ArrayHash extends \stdClass implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * Transforms array to ArrayHash.
	 * @param  array<T>  $array
	 */
	public static function from(array $array, bool $recursive = true): static
	{
		$obj = new static;
		foreach ($array as $key => $value) {
			$obj->$key = $recursive && is_array($value)
				? static::from($value)
				: $value;
		}

		return $obj;
	}


	/**
	 * Returns an iterator over all items.
	 * @return \Iterator<array-key, T>
	 */
	public function &getIterator(): \Iterator
	{
		foreach ((array) $this as $key => $foo) {
			yield $key => $this->$key;
		}
	}


	/**
	 * Returns items count.
	 */
	public function count(): int
	{
		return count((array) $this);
	}


	/**
	 * Replaces or appends a item.
	 * @param  array-key  $key
	 * @param  T  $value
	 */
	public function offsetSet($key, $value): void
	{
		if (!is_scalar($key)) { // prevents null
			throw new Nette\InvalidArgumentException(sprintf('Key must be either a string or an integer, %s given.', get_debug_type($key)));
		}

		$this->$key = $value;
	}


	/**
	 * Returns a item.
	 * @param  array-key  $key
	 * @return T
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($key)
	{
		return $this->$key;
	}


	/**
	 * Determines whether a item exists.
	 * @param  array-key  $key
	 */
	public function offsetExists($key): bool
	{
		return isset($this->$key);
	}


	/**
	 * Removes the element from this list.
	 * @param  array-key  $key
	 */
	public function offsetUnset($key): void
	{
		unset($this->$key);
	}
}
