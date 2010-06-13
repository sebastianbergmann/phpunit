<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.10
 */

require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/FilterIterator.php';

// Set this to the directory that contains the code coverage files.
// It defaults to getcwd(). If you have configured a different directory
// in prepend.php, you need to configure the same directory here.
$GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] = getcwd();

if (isset($_GET['PHPUNIT_SELENIUM_TEST_ID'])) {
    $files = new PHPUnit_Util_FilterIterator(
      new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
          $GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY']
        )
      ),
      $_GET['PHPUNIT_SELENIUM_TEST_ID']
    );

    $coverage = array();

    foreach ($files as $file) {
        $filename = $file->getPathName();
        $data     = unserialize(file_get_contents($filename));
        @unlink($filename);
        unset($filename);

        foreach ($data as $filename => $lines) {
            if (PHPUnit_Util_CodeCoverage::isFile($filename)) {
                if (!isset($coverage[$filename])) {
                    $coverage[$filename] = array(
                      'md5' => md5_file($filename), 'coverage' => $lines
                    );
                } else {
                    foreach ($lines as $line => $flag) {
                        if (!isset($coverage[$filename]['coverage'][$line]) ||
                            $flag > $coverage[$filename]['coverage'][$line]) {
                            $coverage[$filename]['coverage'][$line] = $flag;
                        }
                    }
                }
            }
        }
    }

    print serialize($coverage);
}
?>