<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: XML.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestFailure.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Util/Printer.php';

/**
 * A TestListener that generates an XML-based logfile
 * of the test execution.
 *
 * The XML markup is based upon the one used by the
 * Artima SuiteRunner, see http://www.artima.com/suiterunner/
 * for details.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
class PHPUnit2_Extensions_Log_XML extends PHPUnit2_Util_Printer implements PHPUnit2_Framework_TestListener {
    // {{{ Members

    /**
    * @var    boolean
    * @access private
    */
    private $testFailed = FALSE;

    // }}}
    // {{{ public function addError(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * An error occurred.
    *
    * @param  PHPUnit2_Framework_Test  $test
    * @param  Exception               $e
    * @access public
    */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed($test, $e);
    }

    // }}}
    // {{{ public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

    /**
    * A failure occurred.
    *
    * @param  PHPUnit2_Framework_Test                 $test
    * @param  PHPUnit2_Framework_AssertionFailedError $e
    * @access public
    */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->testFailed($test, $e);
    }

    // }}}
    // {{{ public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * Incomplete test.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  Exception               $e
    * @access public
    */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed($test, $e, 'Incomplete');
    }

    // }}}
    // {{{ public function endTest(PHPUnit2_Framework_Test $test)

    /**
    * A test ended.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function endTest(PHPUnit2_Framework_Test $test) {
        if (!$this->testFailed) {
            $this->write(
              sprintf(
                "<testSucceeded>\n"   .
                "  <name>%s</name>\n" .
                "  <date>%s</date>\n" .
                "</testSucceeded>\n",
                $test->getName(),
                date('d-m-Y H:i:s')
              )
            );
        }
    }

    // }}}
    // {{{ public function startTest(PHPUnit2_Framework_Test $test)

    /**
    * A test started.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->write(
          sprintf(
            "<testStarting>\n"    .
            "  <name>%s</name>\n" .
            "  <date>%s</date>\n" .
            "</testStarting>\n",

            $test->getName(),
            date('d-m-Y H:i:s')
          )
        );

        $this->testFailed = FALSE;
    }

    // }}}
    // {{{ private function testFailed(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * A test failed.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  Exception               $e
    * @param  String                  $type
    * @access private
    */
    private function testFailed(PHPUnit2_Framework_Test $test, Exception $e, $type = 'Failed') {
        $this->write(
          sprintf(
            "<test%s>\n"                .
            "  <name>%s</name>\n"       .
            "  <message>%s</message>\n" .
            "  <date>%s</date>\n"       .
            "</testFailed>\n",

            $type,
            $test->getName(),
            $e->getMessage(),
            date('d-m-Y H:i:s')
          )
        );

        $this->testFailed = TRUE;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
