<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

// Workaround for http://bugs.php.net/bug.php?id=47987,
// see https://github.com/sebastianbergmann/phpunit/issues#issue/125 for details
require_once 'PHPUnit/Framework/Error.php';
require_once 'PHPUnit/Framework/Error/Notice.php';
require_once 'PHPUnit/Framework/Error/Warning.php';
require_once 'PHPUnit/Framework/Error/Deprecated.php';

/**
 * Error handler that converts PHP errors and warnings to exceptions.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class PHPUnit_Util_ErrorHandler
{
    protected static $errorStack = array();

    /**
     * Returns the error stack.
     *
     * @return array
     */
    public static function getErrorStack()
    {
        return self::$errorStack;
    }

    /**
     * @param  integer $errno
     * @param  string  $errstr
     * @param  string  $errfile
     * @param  integer $errline
     * @throws PHPUnit_Framework_Error
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!($errno & error_reporting())) {
            return FALSE;
        }

        self::$errorStack[] = array($errno, $errstr, $errfile, $errline);

        $trace = debug_backtrace(FALSE);
        array_shift($trace);

        foreach ($trace as $frame) {
            if ($frame['function'] == '__toString') {
                return FALSE;
            }
        }

        if ($errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_STRICT) {
            if (PHPUnit_Framework_Error_Notice::$enabled !== TRUE) {
                return FALSE;
            }

            $exception = 'PHPUnit_Framework_Error_Notice';
        }

        else if ($errno == E_WARNING || $errno == E_USER_WARNING) {
            if (PHPUnit_Framework_Error_Warning::$enabled !== TRUE) {
                return FALSE;
            }

            $exception = 'PHPUnit_Framework_Error_Warning';
        }

        else if ($errno == E_DEPRECATED || $errno == E_USER_DEPRECATED) {
            if (PHPUnit_Framework_Error_Deprecated::$enabled !== TRUE) {
                return FALSE;
            }

            $exception = 'PHPUnit_Framework_Error_Deprecated';
        }

        else {
            $exception = 'PHPUnit_Framework_Error';
        }

        throw new $exception($errstr, $errno, $errfile, $errline);
    }
}
