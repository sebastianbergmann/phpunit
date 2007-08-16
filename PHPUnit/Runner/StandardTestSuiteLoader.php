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
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Runner/TestSuiteLoader.php';
require_once 'PHPUnit/Util/Fileloader.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * The standard test suite loader.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Runner_StandardTestSuiteLoader implements PHPUnit_Runner_TestSuiteLoader
{
    /**
     * @param  string  $suiteClassName
     * @param  string  $suiteClassFile
     * @param  boolean $syntaxCheck
     * @return ReflectionClass
     * @throws RuntimeException
     * @access public
     */
    public function load($suiteClassName, $suiteClassFile = '', $syntaxCheck = TRUE)
    {
        $suiteClassName = str_replace('.php', '', $suiteClassName);
        $suiteClassFile = !empty($suiteClassFile) ? $suiteClassFile : str_replace('_', '/', $suiteClassName) . '.php';

        if (!class_exists($suiteClassName, FALSE)) {
            if(!file_exists($suiteClassFile)) {
                $includePaths = PHPUnit_Util_Fileloader::getIncludePaths();

                foreach ($includePaths as $includePath) {
                    $file = $includePath . DIRECTORY_SEPARATOR . $suiteClassFile;

                    if (file_exists($file)) {
                        $suiteClassFile = $file;
                        break;
                    }
                }
            }

            PHPUnit_Util_Fileloader::checkAndLoad($suiteClassFile, $syntaxCheck);
        }

        if (class_exists($suiteClassName, FALSE)) {
            return new ReflectionClass($suiteClassName);
        } else {
            throw new RuntimeException(
              sprintf(
                'Class %s could not be found in %s.',

                $suiteClassName,
                $suiteClassFile
              )
            );
        }
    }

    /**
     * @param  ReflectionClass  $aClass
     * @return ReflectionClass
     * @access public
     */
    public function reload(ReflectionClass $aClass)
    {
        return $aClass;
    }
}
?>
