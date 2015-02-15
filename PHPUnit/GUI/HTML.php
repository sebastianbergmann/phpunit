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
 * @author     Wolfram Kriesing <wolfram@kriesing.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

/**
 * HTML GUI.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Wolfram Kriesing <wolfram@kriesing.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_GUI_HTML
{
    var $_suites = array();

    /**
    * the current implementation of PHPUnit is designed
    * this way that adding a suite to another suite only
    * grabs all the tests and adds them to the suite, so you
    * have no chance to find out which test goes with which suite
    * therefore you can simply pass an array of suites to this constructor here
    *
    * @param  array   The suites to be tested. If not given, then you might
    *                 be using the SetupDecorator, which detects them automatically
    *                 when calling getSuitesFromDir()
    */
    function PHPUnit_GUI_HTML($suites = array())
    {
        if (!is_array($suites)) {
            $this->_suites = array($suites);
        } else {
            $this->_suites = $suites;
        }
    }

    /**
    * Add suites to the GUI
    *
    * @param  object  this should be an instance of PHPUnit_TestSuite
    */
    function addSuites($suites)
    {
        $this->_suites = array_merge($this->_suites,$suites);
    }

    /**
    * this prints the HTML code straight out
    *
    */
    function show()
    {
        $request    = $_REQUEST;
        $showPassed = FALSE;
        $submitted  = @$request['submitted'];

        if ($submitted) {
            $showPassed = @$request['showOK'] ? TRUE : FALSE;
        }

        $suiteResults = array();

        foreach ($this->_suites as $aSuite) {
            $aSuiteResult = array();

            // remove the first directory's name from the test-suite name, since it
            // mostly is something like 'tests' or alike
            $removablePrefix = explode('_',$aSuite->getName());
            $aSuiteResult['name'] = str_replace($removablePrefix[0].'_', '', $aSuite->getName());

            if ($submitted && isset($request[$aSuiteResult['name']])) {
                $result = PHPUnit::run($aSuite);

                $aSuiteResult['counts']['run'] = $result->runCount();
                $aSuiteResult['counts']['error'] = $result->errorCount();
                $aSuiteResult['counts']['failure'] = $result->failureCount();

                $aSuiteResult['results'] = $this->_prepareResult($result,$showPassed);

                $per = 100/$result->runCount();
                $failed = ($per*$result->errorCount())+($per*$result->failureCount());
                $aSuiteResult['percent'] = round(100-$failed,2);
            } else {
                $aSuiteResult['addInfo'] = 'NOT EXECUTED';
            }

            $suiteResults[] = $aSuiteResult;
        }

        $final['name'] = 'OVERALL RESULT';
        $final['counts'] = array();
        $final['percent'] = 0;
        $numExecutedTests = 0;

        foreach ($suiteResults as $aSuiteResult) {
            if (sizeof(@$aSuiteResult['counts'])) {
                foreach ($aSuiteResult['counts'] as $key=>$aCount) {
                    if (!isset($final['counts'][$key])) {
                        $final['counts'][$key] = 0;
                    }

                    $final['counts'][$key] += $aCount;
                }
            }
        }

        if (isset($final['counts']['run'])) {
            $per = 100/$final['counts']['run'];
            $failed = ($per*$final['counts']['error'])+($per*$final['counts']['failure']);
            $final['percent'] = round(100-$failed,2);
        } else {
            $final['percent'] = 0;
        }

        array_unshift($suiteResults,$final);

        include 'PHPUnit/GUI/HTML.tpl';
    }

    function _prepareResult($result,$showPassed)
    {
        $ret = array();
        $failures = $result->failures();

        foreach($failures as $aFailure) {
            $ret['failures'][] = $this->_prepareFailure($aFailure);
        }

        $errors = $result->errors();

        foreach($errors as $aError) {
            $ret['errors'][] = $this->_prepareErrors($aError);
        }

        if ($showPassed) {
            $passed = $result->passedTests();

            foreach($passed as $aPassed) {
                $ret['passed'][] = $this->_preparePassedTests($aPassed);
            }
        }

        return $ret;
    }

    function _prepareFailure($failure)
    {
        $test = $failure->failedTest();
        $ret['testName'] = $test->getName();
        $exception = $failure->thrownException();

        // a serialized string starts with a 'character:decimal:{'
        // if so we try to unserialize it
        // this piece of the regular expression is for detecting a serialized
        // type like 'a:3:' for an array with three element or an object i.e. 'O:12:"class":3'
        $serialized = '(\w:\d+:(?:"[^"]+":\d+:)?\{.*\})';

        // Spaces might make a diff, so we shall show them properly (since a
        // user agent ignores them).
        if (preg_match('/^(.*)expected ' . $serialized . ', actual ' . $serialized . '$/sU', $exception, $matches)) {
            ob_start();
            print_r(unserialize($matches[2]));
            $ret['expected'] = htmlspecialchars($matches[1]) . "<pre>" . htmlspecialchars(rtrim(ob_get_contents())) . "</pre>";
            // Improved compatibility, ob_clean() would be PHP >= 4.2.0 only.
            ob_end_clean();

            ob_start();
            print_r(unserialize($matches[3]));
            $ret['actual'] = htmlspecialchars($matches[1]) . "<pre>" . htmlspecialchars(rtrim(ob_get_contents())) . "</pre>";
            ob_end_clean();
        }

        else if (preg_match('/^(.*)expected (.*), actual (.*)$/sU', $exception, $matches)) {
            $ret['expected'] = nl2br(str_replace(" ", "&nbsp;", htmlspecialchars($matches[1] . $matches[2])));
            $ret['actual'] = nl2br(str_replace(" ", "&nbsp;", htmlspecialchars($matches[1] . $matches[3])));
        } else {
            $ret['message'] = nl2br(str_replace(" ", "&nbsp;", htmlspecialchars($exception)));
        }

        return $ret;
    }

    function _preparePassedTests($passed)
    {
        $ret['testName'] = $passed->getName();
        return $ret;
    }

    function _prepareError($error)
    {
        $ret['testName'] = $error->getName();
        $ret['message'] = $error->toString();
        return $ret;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
