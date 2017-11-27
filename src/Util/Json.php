<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\Exception;

class Json
{
    /**
     * Prettify json string
     *
     * @param string $json
     *
     * @return string
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public static function prettify(string $json)
    {
        $decodedJson = \json_decode($json, true);

        if (\json_last_error()) {
            throw new Exception(
                'Cannot prettify invalid json'
            );
        }

        return \json_encode($decodedJson, JSON_PRETTY_PRINT);
    }

    /*
     * To allow comparison of JSON strings, first process them into a consistent
     * format so that they can be compared as strings.
     * @return array ($error, $canonicalized_json)  The $error parameter is used
     * to indicate an error decoding the json.  This is used to avoid ambiguity
     * with JSON strings consisting entirely of 'null' or 'false'.
     */
    public static function canonicalize(string $json)
    {
        $decodedJson = \json_decode($json, true);

        if (\json_last_error()) {
            return [true, null];
        }

        self::recursiveSort($decodedJson);

        $reencodedJson = \json_encode($decodedJson);

        return [false, $reencodedJson];
    }

    /*
     * JSON object keys are unordered while PHP array keys are ordered.
     * Sort all array keys to ensure both the expected and actual values have
     * their keys in the same order.
     */
    private static function recursiveSort(&$json)
    {
        if (false === \is_array($json)) {
            return;
        }

        \ksort($json);

        foreach ($json as $key => &$value) {
            self::recursiveSort($value);
        }
    }
}
