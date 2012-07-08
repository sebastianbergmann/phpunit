<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  2002-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.0
 */

/**
 * @package    PHPUnit
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  2011-2012 Bastian Feder <php@bastian-feder.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.0
 */
class Framework_Constraint_JsonMatches_ErrorMessageProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider translateTypeToPrefixDataprovider
     * @covers PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider::translateTypeToPrefix
     */
    public function testTranslatTypeToPrefix($expected, $type)
    {
        $this->assertEquals(
            $expected,
            PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider::translateTypeToPrefix($type)
        );
    }

    /**
     * @dataProvider determineJsonErrorDataprovider
     * @covers PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider::determineJsonError
     */
    public function testDetermineJsonError($expected, $error, $prefix)
    {
        $this->assertEquals(
            $expected,
            PHPUnit_Framework_Constraint_JsonMatches_ErrorMessageProvider::determineJsonError(
                $error,
                $prefix
            )
        );
    }

    public static function determineJsonErrorDataprovider()
    {
        return array(
            'JSON_ERROR_NONE'  => array(
                NULL, 'json_error_none', ''
            ),
            'JSON_ERROR_DEPTH' => array(
                'Maximum stack depth exceeded', 'json_error_depth', ''
            ),
            'prefixed JSON_ERROR_DEPTH' => array(
                'TUX: Maximum stack depth exceeded', 'json_error_depth', 'TUX: '
            ),
            'JSON_ERROR_STATE_MISMatch' => array(
                'Underflow or the modes mismatch', 'json_error_state_mismatch', ''
            ),
            'JSON_ERROR_CTRL_CHAR' => array(
                'Unexpected control character found', 'json_error_ctrl_char', ''
            ),
            'JSON_ERROR_SYNTAX' => array(
                'Syntax error, malformed JSON', 'json_error_syntax', ''
            ),
            'JSON_ERROR_UTF8`' => array(
                'Malformed UTF-8 characters, possibly incorrectly encoded',
                'json_error_utf8',
                ''
            ),
            'Invalid error indicator' => array(
                'Unknown error', 'invalid_error_indicator', ''
            ),
        );
    }

    public static function translateTypeToPrefixDataprovider()
    {
        return array(
            'expected' => array('Expected value JSON decode error - ', 'expected'),
            'actual' => array('Actual value JSON decode error - ', 'actual'),
            'default' => array('', ''),
        );
    }
}
