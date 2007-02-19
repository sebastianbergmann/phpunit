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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/GtkUI/Main.php';
require_once 'PHPUnit/GtkUI/TestListener.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * 
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
class PHPUnit_GtkUI_TestRunner
{
    const STATUS_INFO    = 0;
    const STATUS_NOTRUN  = 1;
    const STATUS_OK      = 2;
    const STATUS_SKIPPED = 3;
    const STATUS_FAILED  = 4;
    const STATUS_ERROR   = 5;

    protected $testResult;
    protected $listener;
    protected $tests;

    public function __construct()
    {
        $this->listener = new PHPUnit_GtkUI_TestListener;
    }

    public function loadSuite($filename)
    {
        if ($filename !== NULL &&
            file_exists($filename) && is_readable($filename)) {
            require_once $filename;
        }
    }
    
    public function loadSuites(array $filenames)
    {
        foreach ($filenames as $filename) {
            $this->loadSuite($filename);
        }
    }

    public function doRun($suitesTree)
    {
        $this->result = new PHPUnit_Framework_TestResult;
        $this->result->addListener($this->listener);

        $this->tests = array();
        $suitesTree->foreach(array($this, 'fetchTests'));

        if (count($this->tests) < 1) {
            PHPUnit_GtkUI_Main::getInstance()->statusMessage(
              'No test selected for run!',
              self::STATUS_ERROR
            );

            return;
        }

        $fraction = (100 / count($this->tests)) / 100;
        $progress = 0;

        PHPUnit_GtkUI_Main::getInstance()->testProgress($progress);

        $status = PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          'Started run of ' . count($this->tests) . ' test cases.',
          self::STATUS_INFO
        );

        $this->listener->setGlobalStatus($status);

        foreach ($this->tests as $test) {
            $test->run($this->result);
            $progress += $fraction;
            PHPUnit_GtkUI_Main::getInstance()->testProgress($progress);
        }

        $infoParent = PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          'End of test run.',
          self::STATUS_INFO,
          $status
        );

        PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          'Ran ' . $this->result->count() . ' test cases.',
          self::STATUS_INFO,
          $infoParent
        );

        PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          $this->result->skippedCount() . ' test cases were skipped.',
          self::STATUS_INFO,
          $infoParent
        );

        PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          $this->result->errorCount() . ' test cases resulted in an error.',
          self::STATUS_INFO,
          $infoParent
        );

        PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          $this->result->failureCount() . ' test cases failed.',
          self::STATUS_INFO,
          $infoParent
        );

        $successCount = $this->result->count()
          - $this->result->skippedCount()
          - $this->result->errorCount()
          - $this->result->failureCount();

        PHPUnit_GtkUI_Main::getInstance()->statusMessage(
          "$successCount test cases ran successfully.",
          self::STATUS_INFO,
          $infoParent
        );
    }

    public function fetchTests($model, $path, $iter)
    {
        $test = $model->get_value($iter, 0);

        if ($test instanceof PHPUnit_Framework_TestCase &&
            $model->get_value($iter, 1) === TRUE) {
            $this->tests[] = $test;
        }
    }
}
?>
