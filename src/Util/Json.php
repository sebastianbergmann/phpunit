<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use function count;
use function is_array;
use function is_object;
use function json_decode;
use function json_encode;
use function json_last_error;
use function ksort;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Json
{
    /**
     * @throws InvalidJsonException
     */
    public static function prettify(string $json): string
    {
        $decodedJson = json_decode($json, false);

        if (json_last_error()) {
            throw new InvalidJsonException;
        }

        return json_encode($decodedJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * To allow comparison of JSON strings, first process them into a consistent
     * format so that they can be compared as strings.
     *
     * @return array ($error, $canonicalized_json)  The $error parameter is used
     *               to indicate an error decoding the json. This is used to avoid ambiguity
     *               with JSON strings consisting entirely of 'null' or 'false'.
     */
    public static function canonicalize(string $json): array
    {
        $decodedJson = json_decode($json);

        if (json_last_error()) {
            return [true, null];
        }

        self::recursiveSort($decodedJson);

        $reencodedJson = json_encode($decodedJson);

        return [false, $reencodedJson];
    }

    /**
     * JSON object keys are unordered while PHP array keys are ordered.
     *
     * Sort all array keys to ensure both the expected and actual values have
     * their keys in the same order.
     */
    private static function recursiveSort(mixed &$json): void
    {
        if (!is_array($json)) {
            // If the object is not empty, change it to an associative array
            // so we can sort the keys (and we will still re-encode it
            // correctly, since PHP encodes associative arrays as JSON objects.)
            // But EMPTY objects MUST remain empty objects. (Otherwise we will
            // re-encode it as a JSON array rather than a JSON object.)
            // See #2919.
            if (is_object($json) && count((array) $json) > 0) {
                $json = (array) $json;
            } else {
                return;
            }
        }

        ksort($json);

        foreach ($json as &$value) {
            self::recursiveSort($value);
        }
    }
}
