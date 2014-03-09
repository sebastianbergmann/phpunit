<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 * Utility class for code filtering.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Util_Filter
{
    /**
     * Filters stack frames from PHPUnit classes.
     *
     * @param  Exception $e
     * @param  boolean   $asString
     * @return string
     */
    public static function getFilteredStacktrace(Exception $e, $asString = true)
    {
        $prefix = false;
        $script = realpath($GLOBALS['_SERVER']['SCRIPT_NAME']);

        if (defined('__PHPUNIT_PHAR_ROOT__')) {
            $prefix = __PHPUNIT_PHAR_ROOT__;
        }

        if ($asString === true) {
            $filteredStacktrace = '';
        } else {
            $filteredStacktrace = array();
        }

        if ($e instanceof PHPUnit_Framework_SyntheticError) {
            $eTrace = $e->getSyntheticTrace();
            $eFile  = $e->getSyntheticFile();
            $eLine  = $e->getSyntheticLine();
        } else {
            if ($e->getPrevious()) {
                $eTrace = $e->getPrevious()->getTrace();
            } else {
                $eTrace = $e->getTrace();
            }
            $eFile  = $e->getFile();
            $eLine  = $e->getLine();
        }

        if (!self::frameExists($eTrace, $eFile, $eLine)) {
            array_unshift(
              $eTrace, array('file' => $eFile, 'line' => $eLine)
            );
        }

        $blacklist = new PHPUnit_Util_Blacklist;

        foreach ($eTrace as $frame) {
            if (isset($frame['file']) && is_file($frame['file']) &&
                !$blacklist->isBlacklisted($frame['file']) &&
                ($prefix === false || strpos($frame['file'], $prefix) !== 0) &&
                $frame['file'] !== $script) {
                if ($asString === true) {
                    $filteredStacktrace .= sprintf(
                      "%s:%s\n",

                      $frame['file'],
                      isset($frame['line']) ? $frame['line'] : '?'
                    );
                } else {
                    $filteredStacktrace[] = $frame;
                }
            }
        }

        return $filteredStacktrace;
    }

    /**
     * @param  array   $trace
     * @param  string  $file
     * @param  int     $line
     * @return boolean
     * @since  Method available since Release 3.3.2
     */
    private static function frameExists(array $trace, $file, $line)
    {
        foreach ($trace as $frame) {
            if (isset($frame['file']) && $frame['file'] == $file &&
                isset($frame['line']) && $frame['line'] == $line) {
                return true;
            }
        }

        return false;
    }
}
