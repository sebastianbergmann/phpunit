<?php
/**
 * PHP-GTK2 Test Runner for PHPUnit
 *
 * Copyright (c) 2007, Tobias Schlitt <toby@php.net>.
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
 *   * Neither the name of Tobias Schlitt nor the names of his
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
 * @copyright  2007 Tobias Schlitt <toby@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/GtkUI/Controller/StatusTree.php';
require_once 'PHPUnit/GtkUI/TestRunner.php';
require_once 'PHPUnit/GtkUI/TestStatusMapper.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * 
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2007 Tobias Schlitt <toby@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_GtkUI_TestListener implements PHPUnit_Framework_TestListener
{
    private $lastTestStatus;
    private $parentStatus;
    private $globalStatus;

    public function setGlobalStatus($status)
    {
        $this->globalStatus = $status;
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->lastTestStatus = PHPUnit_GtkUI_Runner::STATUS_ERROR;

        $parent = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_ERROR,
          'Test produced error.',
          $this->parentStatus
        );

        $status = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_ERROR,
          $e->getMessage(),
          $parent
        );

        PHPUnit_GtkUI_TestStatusMapper::getInstance()->setMapping(
          $test, $status
        );
    }

    public function addFailure( PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time )
    {
        $this->lastTestStatus = PHPUnit_GtkUI_Runner::STATUS_FAILED;

        $parent = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_FAILED,
          'Test failed.',
          $this->parentStatus
        );

        $status = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_FAILED,
          $e->toString(),
          $parent
        );

        PHPUnit_GtkUI_TestStatusMapper::getInstance()->setMapping(
          $test, $status
        );
    }
    
    public function addIncompleteTest( PHPUnit_Framework_Test $test, Exception $e, $time )
    {
        $this->lastTestStatus = PHPUnit_GtkUI_Runner::STATUS_ERROR;

        $parent = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_ERROR,
          'Test incomplete.',
          $this->parentStatus
        );

        $status = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_ERROR,
          $e->getMessage(),
          $parent
        );

        PHPUnit_GtkUI_TestStatusMapper::getInstance()->setMapping(
          $test, $status
        );
    }
    
    public function addSkippedTest( PHPUnit_Framework_Test $test, Exception $e, $time )
    {
        $this->lastTestStatus = PHPUnit_GtkUI_Runner::STATUS_SKIPPED;

        $parent = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_SKIPPED,
          'Skipped test.',
          $this->parentStatus
        );

        $status = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_SKIPPED,
          $e->getMessage(),
          $parent
        );

        PHPUnit_GtkUI_TestStatusMapper::getInstance()->setMapping(
          $test, $status
        );
    }

    public function startTestSuite( PHPUnit_Framework_TestSuite $suite )
    {
    }
    
    public function endTestSuite( PHPUnit_Framework_TestSuite $suite )
    {
    }

    public function startTest( PHPUnit_Framework_Test $test )
    {
        $this->lastTestStatus = PHPUnit_GtkUI_Runner::STATUS_OK;

        $this->parentStatus = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_INFO,
          "Run of {$test->toString()}.",
          $this->globalStatus
        );

        $status = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_INFO,
          'Test run started.',
          $this->parentStatus
        );

        PHPUnit_GtkUI_TestStatusMapper::getInstance()->setMapping(
          $test, $status
        );
    }

    public function endTest( PHPUnit_Framework_Test $test, $time )
    {
        PHPUnit_GtkUI_Controller_SuiteTree::getInstance()->markTest(
          $test, $this->lastTestStatus
        );

        PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
          PHPUnit_GtkUI_Runner::STATUS_INFO,
          "Test run finished after $time seconds.",
          $this->parentStatus
        );
    }
}
?>
