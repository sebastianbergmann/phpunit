<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit                                                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2003 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id$
//

/**
*   This class provides a HTML GUI.
*
*   @author Wolfram Kriesing <wolfram@kriesing.de>
*
*/
class PHPUnit_GUI_HTML
{

    var $_suites = array();

    /**
    *   the current implementation of PHPUnit is designed
    *   this way that adding a suite to another suite only
    *   grabs all the tests and adds them to the suite, so you
    *   have no chance to find out which test goes with which suite
    *   therefore you can simply pass an array of suites to this constructor here
    *
    *   @param  array   The suites to be tested. If not given, then you might
    *                   be using the SetupDecorator, which detects them automatically
    *                   when calling getSuitesFromDir()
    *
    */
    function PHPUnit_GUI_HTML($suites=array())
    {
        if (!is_array($suites)) {
            $this->_suites = array($suites);
        } else {
            $this->_suites = $suites;
        }
    }
    
    /**
    *   Add suites to the GUI
    *
    *   @param  object  this should be an instance of PHPUnit_TestSuite
    */
    function addSuites($suites)
    {
        $this->_suites = array_merge($this->_suites,$suites);
    }

    /**
    *   this prints the HTML code straight out
    *
    */
    function show()
    {
        $showPassed=false;
        $submitted = @$_REQUEST['submitted'];
        if ($submitted) {
            $showPassed = @$_REQUEST['showOK'] ? true : false;
        }
        
        $suiteResults = array();
        foreach ($this->_suites as $aSuite) {
            $aSuiteResult = array();
            // remove the first directory's name from the test-suite name, since it
            // mostly is something like 'tests' or alike 
            $removablePrefix = explode('_',$aSuite->getName());
            $aSuiteResult['name'] = str_replace($removablePrefix[0].'_', '', $aSuite->getName());
            if ($submitted && isset($_REQUEST[$aSuiteResult['name']])) {
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
		$serializePrefix = '(\\w:\\d+:|O:\\d+:"[a-z_]+":\\d+:)?';
        if (preg_match('/expected\s+'.$serializePrefix.'{.*}, actual\s+'.$serializePrefix.'{.*}/i',$exception)) {
            ob_start();
            print_r(unserialize(preg_replace('/expected\s+(.*), actual.*/','$1',$exception)));
            $ret['expected'] = '<pre>'.ob_get_contents().'</pre>';
            ob_clean();
            print_r(unserialize(preg_replace('/expected\s.*, actual (.*)/','$1',$exception)));
            $ret['actual'] = '<pre>'.ob_get_contents().'</pre>';
            ob_end_clean();
        } else {
            // spaces might make a diff, so we shall show them properly (since a user agent ignores them)
            if (preg_match('/expected\s.*, actual.*/',$exception)) {
                $ret['expected'] = preg_replace('/expected\s(.*), actual.*/','$1',$exception);
                $ret['actual'] = preg_replace('/expected\s.*, actual (.*)/','$1',$exception);
            } else {
                $ret['message'] = str_replace(' ','&nbsp;',$exception);
            }
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

?>
