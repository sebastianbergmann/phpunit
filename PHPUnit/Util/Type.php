<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Utility class for textual type (and value) representation.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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

    public static function shortenedExport($value)
    {
        if (is_string($value)) {
            return self::shortenedString($value);
        }

        elseif (is_array($value)) {
            if (count($value) == 0) {
                return 'array()';
            }

            $a1 = array_slice($value, 0, 1, TRUE);
            $k1 = key($a1);
            $v1 = $a1[$k1];

            if (is_string($v1)) {
                $v1 = self::shortenedString($v1);
            }

            elseif (is_array($v1)) {
                $v1 = 'array(...)';
            } else {
                $v1 = self::toString($v1);
            }

            $a2 = FALSE;

            if (count($value) > 1) {
                $a2 = array_slice($value, -1, 1, TRUE);
                $k2 = key($a2);
                $v2 = $a2[$k2];

                if (is_string($v2)) {
                    $v2 = self::shortenedString($v2);
                }

                elseif (is_array($v2)) {
                    $v2 = 'array(...)';
                } else {
                    $v2 = self::toString($v2);
                }
            }

            $text = 'array( ' . self::toString($k1) . ' => ' . $v1;

            if ($a2 !== FALSE) {
                $text .= ', ..., ' . self::toString($k2) . ' => ' . $v2 . ' )';
            } else {
                $text .= ' )';
            }

            return $text;
        }

        elseif (is_object($value)) {
            return get_class($value) . '(...)';
        }

        return self::toString($value);
    }

    public static function shortenedString($string)
    {
        $string = preg_replace('#\n|\r\n|\r#', ' ', $string);

        if (strlen($string) > 14) {
            return PHPUnit_Util_Type::toString(
              substr($string, 0, 7) . '...' . substr($string, -7)
            );
        } else {
            return PHPUnit_Util_Type::toString($string);
        }
    }

    public static function toString($value, $short = FALSE)
    {
        if (is_array($value) || is_object($value)) {
            if (!$short) {
                return "\n" . print_r($value, TRUE);
            } else {
                if (is_array($value)) {
                    return '<array>';
                } else {
                    return '<' . get_class($value) . '>';
                }
            }
        }

        if (is_string($value) && strpos($value, "\n") !== FALSE) {
            return '<text>';
        }

        if (!is_null($value)) {
            $type = gettype($value) . ':';
        } else {
            $type  = '';
            $value = 'null';
        }

        if (is_bool($value)) {
            if ($value === TRUE) {
                $value = 'true';
            }

            else if ($value === FALSE) {
                $value = 'false';
            }
        }

        return '<' . $type . $value . '>';
    }
}
