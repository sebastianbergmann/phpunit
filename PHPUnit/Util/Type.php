<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Utility class for textual type (and value) representation.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Type
{
    public static function isType($type)
    {
        return in_array(
          $type,
          array(
            'numeric',
            'integer',
            'int',
            'float',
            'string',
            'boolean',
            'bool',
            'null',
            'array',
            'object',
            'resource',
            'scalar'
          )
        );
    }

    /**
     * Exports a value into a string
     *
     * The output of this method is similar to the output of print_r(), but
     * improved in various aspects:
     *
     *  - NULL is rendered as "null" (instead of "")
     *  - TRUE is rendered as "true" (instead of "1")
     *  - FALSE is rendered as "false" (instead of "")
     *  - Strings are always quoted with single quotes
     *  - Carriage returns and newlines are normalized to \n
     *  - Recursion and repeated rendering is treated properly
     *
     * @param  mixed $value The value to export
     * @param  integer $indentation The indentation level of the 2nd+ line
     * @return string
     * @since  Method available since Release 3.6.0
     */
    public static function export($value, $indentation = 0)
    {
        return self::recursiveExport($value, $indentation);
    }

    /**
     * Recursive implementation of export
     *
     * @param  mixed $value The value to export
     * @param  integer $indentation The indentation level of the 2nd+ line
     * @param  array $processedObjects Contains all objects that were already
     *                                 rendered
     * @return string
     * @since  Method available since Release 3.6.0
     * @see    PHPUnit_Util_Type::export
     */
    protected static function recursiveExport($value, $indentation, &$processedObjects = array())
    {
        if ($value === NULL) {
            return 'null';
        }

        if ($value === TRUE) {
            return 'true';
        }

        if ($value === FALSE) {
            return 'false';
        }

        if (is_string($value)) {
            // Match for most non printable chars somewhat taking multibyte chars into account
            if (preg_match('/[^\x09-\x0d\x20-\xff]/', $value)) {
                return 'Binary String: 0x' . bin2hex($value);
            }

            return "'" .
                   str_replace(array("\r\n", "\n\r", "\r"), array("\n", "\n", "\n"), $value) .
                   "'";
        }

        $origValue = $value;

        if (is_object($value)) {
            if (in_array($value, $processedObjects, TRUE)) {
                return sprintf(
                  '%s Object (*RECURSION*)',

                  get_class($value)
                );
            }

            $processedObjects[] = $value;

            // Convert object to array
            $value = self::toArray($value);
        }

        if (is_array($value)) {
            $whitespace = str_repeat('    ', $indentation);

            // There seems to be no other way to check arrays for recursion
            // http://www.php.net/manual/en/language.types.array.php#73936
            preg_match_all('/\n            \[(\w+)\] => Array\s+\*RECURSION\*/', print_r($value, TRUE), $matches);
            $recursiveKeys = array_unique($matches[1]);

            // Convert to valid array keys
            // Numeric integer strings are automatically converted to integers
            // by PHP
            foreach ($recursiveKeys as $key => $recursiveKey) {
                if ((string)(integer)$recursiveKey === $recursiveKey) {
                    $recursiveKeys[$key] = (integer)$recursiveKey;
                }
            }

            $content = '';

            foreach ($value as $key => $val) {
                if (in_array($key, $recursiveKeys, TRUE)) {
                    $val = 'Array (*RECURSION*)';
                }

                else {
                    $val = self::recursiveExport($val, $indentation+1, $processedObjects);
                }

                $content .=  $whitespace . '    ' . self::export($key) . ' => ' . $val . "\n";
            }

            if (strlen($content) > 0) {
                $content = "\n" . $content . $whitespace;
            }

            return sprintf(
              "%s (%s)",

              is_object($origValue) ? get_class($origValue) . ' Object' : 'Array',
              $content
            );
        }

        if (is_double($value) && (double)(integer)$value === $value) {
            return $value . '.0';
        }

        return (string)$value;
    }

    /**
     * Exports a value into a single-line string
     *
     * The output of this method is similar to the output of
     * PHPUnit_Util_Type::export. This method guarantees thought that the
     * result contains now newlines.
     *
     * Newlines are replaced by the visible string '\n'. Contents of arrays
     * and objects (if any) are replaced by '...'.
     *
     * @param  mixed $value The value to export
     * @param  integer $indentation The indentation level of the 2nd+ line
     * @return string
     * @see    PHPUnit_Util_Type::export
     */
    public static function shortenedExport($value)
    {
        if (is_string($value)) {
            return self::shortenedString($value);
        }

        $origValue = $value;

        if (is_object($value)) {
            $value = self::toArray($value);
        }

        if (is_array($value)) {
            return sprintf(
              "%s (%s)",

              is_object($origValue) ? get_class($origValue) . ' Object' : 'Array',
              count($value) > 0 ? '...' : ''
            );
        }

        return self::export($value);
    }

    /**
     * Shortens a string and converts all new lines to '\n'
     *
     * @param  string $string The string to shorten
     * @param  integer $max The maximum length for the string
     * @return string
     */
    public static function shortenedString($string, $maxLength = 40)
    {
        $string = self::export($string);

        if (strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength - 10) . '...' . substr($string, -7);
        }

        return str_replace("\n", '\n', $string);
    }

    /**
     * Converts an object to an array containing all of its private, protected
     * and public properties.
     *
     * @param  object $object
     * @return array
     * @since  Method available since Release 3.6.0
     */
    public static function toArray($object)
    {
        $array = array();

        foreach ((array)$object as $key => $value) {
            // properties are transformed to keys in the following way:

            // private   $property => "\0Classname\0property"
            // protected $property => "\0*\0property"
            // public    $property => "property"

            if (preg_match('/^\0.+\0(.+)$/', $key, $matches)) {
                $key = $matches[1];
            }

            $array[$key] = $value;
        }

        // Some internal classes like SplObjectStorage don't work with the
        // above (fast) mechanism nor with reflection
        // Format the output similarly to print_r() in this case
        if ($object instanceof SplObjectStorage) {
            foreach ($object as $key => $value) {
                $array[spl_object_hash($value)] = array(
                    'obj' => $value,
                    'inf' => $object->getInfo(),
                );
            }
        }

        return $array;
    }
}
