<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;


class Helpers
{
	/**
	 * Executes a callback and returns the captured output as a string.
	 */
	public static function capture(callable $func): string
	{
		ob_start(function () {});
		try {
			$func();
			return ob_get_clean();
		} catch (\Throwable $e) {
			ob_end_clean();
			throw $e;
		}
	}


	/**
	 * Returns the last occurred PHP error or an empty string if no error occurred. Unlike error_get_last(),
	 * it is nit affected by the PHP directive html_errors and always returns text, not HTML.
	 */
	public static function getLastError(): string
	{
		$message = error_get_last()['message'] ?? '';
		$message = ini_get('html_errors') ? Html::htmlToText($message) : $message;
		$message = preg_replace('#^\w+\(.*?\): #', '', $message);
		return $message;
	}


	/**
	 * Converts false to null, does not change other values.
	 */
	public static function falseToNull(mixed $value): mixed
	{
		return $value === false ? null : $value;
	}


	/**
	 * Returns value clamped to the inclusive range of min and max.
	 */
	public static function clamp(int|float $value, int|float $min, int|float $max): int|float
	{
		if ($min > $max) {
			throw new Nette\InvalidArgumentException("Minimum ($min) is not less than maximum ($max).");
		}

		return min(max($value, $min), $max);
	}


	/**
	 * Looks for a string from possibilities that is most similar to value, but not the same (for 8-bit encoding).
	 * @param  string[]  $possibilities
	 */
	public static function getSuggestion(array $possibilities, string $value): ?string
	{
		$best = null;
		$min = (strlen($value) / 4 + 1) * 10 + .1;
		foreach (array_unique($possibilities) as $item) {
			if ($item !== $value && ($len = levenshtein($item, $value, 10, 11, 10)) < $min) {
				$min = $len;
				$best = $item;
			}
		}

		return $best;
	}


	/**
	 * Compares two values in the same way that PHP does. Recognizes operators: >, >=, <, <=, =, ==, ===, !=, !==, <>
	 */
	public static function compare(mixed $left, string $operator, mixed $right): bool
	{
		return match ($operator) {
			'>' => $left > $right,
			'>=' => $left >= $right,
			'<' => $left < $right,
			'<=' => $left <= $right,
			'=', '==' => $left == $right,
			'===' => $left === $right,
			'!=', '<>' => $left != $right,
			'!==' => $left !== $right,
			default => throw new Nette\InvalidArgumentException("Unknown operator '$operator'"),
		};
	}
}
