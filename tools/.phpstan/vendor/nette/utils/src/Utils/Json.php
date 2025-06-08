<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;


/**
 * JSON encoder and decoder.
 */
final class Json
{
	use Nette\StaticClass;

	/** @deprecated use Json::decode(..., forceArrays: true) */
	public const FORCE_ARRAY = JSON_OBJECT_AS_ARRAY;

	/** @deprecated use Json::encode(..., pretty: true) */
	public const PRETTY = JSON_PRETTY_PRINT;

	/** @deprecated use Json::encode(..., asciiSafe: true) */
	public const ESCAPE_UNICODE = 1 << 19;


	/**
	 * Converts value to JSON format. Use $pretty for easier reading and clarity, $asciiSafe for ASCII output
	 * and $htmlSafe for HTML escaping, $forceObjects enforces the encoding of non-associateve arrays as objects.
	 * @throws JsonException
	 */
	public static function encode(
		mixed $value,
		bool|int $pretty = false,
		bool $asciiSafe = false,
		bool $htmlSafe = false,
		bool $forceObjects = false,
	): string
	{
		if (is_int($pretty)) { // back compatibility
			$flags = ($pretty & self::ESCAPE_UNICODE ? 0 : JSON_UNESCAPED_UNICODE) | ($pretty & ~self::ESCAPE_UNICODE);
		} else {
			$flags = ($asciiSafe ? 0 : JSON_UNESCAPED_UNICODE)
				| ($pretty ? JSON_PRETTY_PRINT : 0)
				| ($forceObjects ? JSON_FORCE_OBJECT : 0)
				| ($htmlSafe ? JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG : 0);
		}

		$flags |= JSON_UNESCAPED_SLASHES
			| (defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0); // since PHP 5.6.6 & PECL JSON-C 1.3.7

		$json = json_encode($value, $flags);
		if ($error = json_last_error()) {
			throw new JsonException(json_last_error_msg(), $error);
		}

		return $json;
	}


	/**
	 * Parses JSON to PHP value. The $forceArrays enforces the decoding of objects as arrays.
	 * @throws JsonException
	 */
	public static function decode(string $json, bool|int $forceArrays = false): mixed
	{
		$flags = is_int($forceArrays) // back compatibility
			? $forceArrays
			: ($forceArrays ? JSON_OBJECT_AS_ARRAY : 0);
		$flags |= JSON_BIGINT_AS_STRING;

		$value = json_decode($json, flags: $flags);
		if ($error = json_last_error()) {
			throw new JsonException(json_last_error_msg(), $error);
		}

		return $value;
	}
}
