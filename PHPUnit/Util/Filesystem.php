<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Filesystem helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 * @abstract
 */
class PHPUnit_Util_Filesystem
{
    /**
     * Returns the common path of a set of files.
     *
     * @param  array $paths
     * @return string
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function getCommonPath(array $paths)
    {
        $count = count($paths);

        if ($count == 1) {
            return dirname($paths[0]) . DIRECTORY_SEPARATOR;
        }

        $_paths = array();

        for ($i = 0; $i < $count; $i++) {
            $_paths[$i] = explode(DIRECTORY_SEPARATOR, $paths[$i]);

            if (empty($_paths[$i][0])) {
                $_paths[$i][0] = DIRECTORY_SEPARATOR;
            }
        }

        $common = '';
        $done   = FALSE;
        $j      = 0;
        $count--;

        while (!$done) {
            for ($i = 0; $i < $count; $i++) {
                if ($_paths[$i][$j] != $_paths[$i+1][$j]) {
                    $done = TRUE;
                    break;
                }
            }

            if (!$done) {
                $common .= $_paths[0][$j];

                if ($j > 0) {
                    $common .= DIRECTORY_SEPARATOR;
                }
            }

            $j++;
        }

        return $common;
    }

    /**
     * Returns a filesystem safe version of the passed filename.
     * This function does not operate on full paths, just filenames.
     *
     * @param  string $filename
     * @return string
     * @access public
     * @static
     * @author Michael Lively Jr. <m@digitalsandwich.com>
     */
    public static function getSafeFilename($filename)
    {
        /* characters allowed: A-Z, a-z, 0-9, _ and . */
        return preg_replace('#[^\w.]#', '_', $filename);
    }
}
?>
