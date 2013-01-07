<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Framework_Constraint
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  2002-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.0
 */

/**
 * Provides human readable messages for each JSON error.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  2011 Bastian Feder <php@bastian-feder.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.7.0
 */
class PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider
{
    /**
     * Translatets accourd JSON error to a human readable string.
     *
     * @param string $error
     * @return string
     */
    public static function determineJsonError($error, $prefix = '')
    {
        switch (strtoupper($error)) {
            case 'JSON_ERROR_NONE':
                return;
            case 'JSON_ERROR_DEPTH':
                return $prefix . 'Maximum stack depth exceeded';
            case 'JSON_ERROR_STATE_MISMATCH':
                return $prefix . 'Underflow or the modes mismatch';
            case 'JSON_ERROR_CTRL_CHAR':
                return $prefix . 'Unexpected control character found';
            case 'JSON_ERROR_SYNTAX':
                return $prefix . 'Syntax error, malformed JSON';
            case 'JSON_ERROR_UTF8':
                return $prefix . 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return $prefix . 'Unknown error';
        }
    }

    /**
     * Translates a given type to a human readable message prefix.
     *
     * @param string $type
     * @return string
     */
    public static function translateTypeToPrefix($type)
    {
        switch (strtolower($type)) {
            case 'expected':
                $prefix = 'Expected value JSON decode error - ';
                break;
            case 'actual':
                $prefix = 'Actual value JSON decode error - ';
                break;
            default:
                $prefix = '';
                break;
        }
        return $prefix;
    }
}
