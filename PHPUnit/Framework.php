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
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

require 'PHPUnit/Framework/Exception.php';
require 'PHPUnit/Framework/SelfDescribing.php';
require 'PHPUnit/Framework/AssertionFailedError.php';
require 'PHPUnit/Framework/Assert.php';
require 'PHPUnit/Framework/Error.php';
require 'PHPUnit/Framework/Error/Notice.php';
require 'PHPUnit/Framework/Error/Warning.php';
require 'PHPUnit/Framework/IncompleteTest.php';
require 'PHPUnit/Framework/SkippedTest.php';
require 'PHPUnit/Framework/Test.php';
require 'PHPUnit/Framework/TestFailure.php';
require 'PHPUnit/Framework/TestListener.php';
require 'PHPUnit/Framework/TestResult.php';
require 'PHPUnit/Framework/ExpectationFailedException.php';
require 'PHPUnit/Framework/IncompleteTestError.php';
require 'PHPUnit/Framework/SkippedTestError.php';
require 'PHPUnit/Framework/SkippedTestSuiteError.php';
require 'PHPUnit/Framework/TestCase.php';
require 'PHPUnit/Framework/TestSuite.php';
require 'PHPUnit/Framework/TestSuite/DataProvider.php';
require 'PHPUnit/Framework/Warning.php';
require 'PHPUnit/Framework/Constraint.php';
require 'PHPUnit/Framework/ComparisonFailure.php';
?>