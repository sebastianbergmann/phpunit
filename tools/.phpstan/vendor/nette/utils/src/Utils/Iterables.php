<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;


/**
 * Utilities for iterables.
 */
final class Iterables
{
	use Nette\StaticClass;

	/**
	 * Tests for the presence of value.
	 */
	public static function contains(iterable $iterable, mixed $value): bool
	{
		foreach ($iterable as $v) {
			if ($v === $value) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Tests for the presence of key.
	 */
	public static function containsKey(iterable $iterable, mixed $key): bool
	{
		foreach ($iterable as $k => $v) {
			if ($k === $key) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Returns the first item (matching the specified predicate if given). If there is no such item, it returns result of invoking $else or null.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @param  ?callable(V, K, iterable<K, V>): bool  $predicate
	 * @return ?V
	 */
	public static function first(iterable $iterable, ?callable $predicate = null, ?callable $else = null): mixed
	{
		foreach ($iterable as $k => $v) {
			if (!$predicate || $predicate($v, $k, $iterable)) {
				return $v;
			}
		}
		return $else ? $else() : null;
	}


	/**
	 * Returns the key of first item (matching the specified predicate if given). If there is no such item, it returns result of invoking $else or null.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @param  ?callable(V, K, iterable<K, V>): bool  $predicate
	 * @return ?K
	 */
	public static function firstKey(iterable $iterable, ?callable $predicate = null, ?callable $else = null): mixed
	{
		foreach ($iterable as $k => $v) {
			if (!$predicate || $predicate($v, $k, $iterable)) {
				return $k;
			}
		}
		return $else ? $else() : null;
	}


	/**
	 * Tests whether at least one element in the iterator passes the test implemented by the provided function.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @param  callable(V, K, iterable<K, V>): bool  $predicate
	 */
	public static function some(iterable $iterable, callable $predicate): bool
	{
		foreach ($iterable as $k => $v) {
			if ($predicate($v, $k, $iterable)) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Tests whether all elements in the iterator pass the test implemented by the provided function.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @param  callable(V, K, iterable<K, V>): bool  $predicate
	 */
	public static function every(iterable $iterable, callable $predicate): bool
	{
		foreach ($iterable as $k => $v) {
			if (!$predicate($v, $k, $iterable)) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Iterator that filters elements according to a given $predicate. Maintains original keys.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @param  callable(V, K, iterable<K, V>): bool  $predicate
	 * @return \Generator<K, V>
	 */
	public static function filter(iterable $iterable, callable $predicate): \Generator
	{
		foreach ($iterable as $k => $v) {
			if ($predicate($v, $k, $iterable)) {
				yield $k => $v;
			}
		}
	}


	/**
	 * Iterator that transforms values by calling $transformer. Maintains original keys.
	 * @template K
	 * @template V
	 * @template R
	 * @param  iterable<K, V>  $iterable
	 * @param  callable(V, K, iterable<K, V>): R  $transformer
	 * @return \Generator<K, R>
	 */
	public static function map(iterable $iterable, callable $transformer): \Generator
	{
		foreach ($iterable as $k => $v) {
			yield $k => $transformer($v, $k, $iterable);
		}
	}


	/**
	 * Iterator that transforms keys and values by calling $transformer. If it returns null, the element is skipped.
	 * @template K
	 * @template V
	 * @template ResV
	 * @template ResK
	 * @param  iterable<K, V>  $iterable
	 * @param  callable(V, K, iterable<K, V>): ?array{ResV, ResK}  $transformer
	 * @return \Generator<ResV, ResK>
	 */
	public static function mapWithKeys(iterable $iterable, callable $transformer): \Generator
	{
		foreach ($iterable as $k => $v) {
			$pair = $transformer($v, $k, $iterable);
			if ($pair) {
				yield $pair[0] => $pair[1];
			}
		}
	}


	/**
	 * Wraps around iterator and caches its keys and values during iteration.
	 * This allows the data to be re-iterated multiple times.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @return \IteratorAggregate<K, V>
	 */
	public static function memoize(iterable $iterable): iterable
	{
		return new class (self::toIterator($iterable)) implements \IteratorAggregate {
			public function __construct(
				private \Iterator $iterator,
				private array $cache = [],
			) {
			}


			public function getIterator(): \Generator
			{
				if (!$this->cache) {
					$this->iterator->rewind();
				}
				$i = 0;
				while (true) {
					if (isset($this->cache[$i])) {
						[$k, $v] = $this->cache[$i];
					} elseif ($this->iterator->valid()) {
						$k = $this->iterator->key();
						$v = $this->iterator->current();
						$this->iterator->next();
						$this->cache[$i] = [$k, $v];
					} else {
						break;
					}
					yield $k => $v;
					$i++;
				}
			}
		};
	}


	/**
	 * Creates an iterator from anything that is iterable.
	 * @template K
	 * @template V
	 * @param  iterable<K, V>  $iterable
	 * @return \Iterator<K, V>
	 */
	public static function toIterator(iterable $iterable): \Iterator
	{
		return match (true) {
			$iterable instanceof \Iterator => $iterable,
			$iterable instanceof \IteratorAggregate => self::toIterator($iterable->getIterator()),
			is_array($iterable) => new \ArrayIterator($iterable),
		};
	}
}
