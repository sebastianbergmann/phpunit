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
// $Id: TestSetup.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Extensions/TestDecorator.php';

/**
 * A Decorator to set up and tear down additional fixture state.
 * Subclass TestSetup and insert it into your tests when you want
 * to set up additional state once before the tests are run.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 */
class PHPUnit2_Extensions_TestSetup extends PHPUnit2_Extensions_TestDecorator {
    // {{{ public function run($result = NULL)

    /**
    * Runs the decorated test and collects the
    * result in a TestResult.
    *
    * @param  PHPUnit2_Framework_TestResult $result
    * @return PHPUnit2_Framework_TestResult
    * @access public
    */
    public function run($result = NULL) {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        // XXX: Workaround for missing ability to declare type-hinted parameters as optional.
        else if (!($result instanceof PHPUnit2_Framework_TestResult)) {
            throw new Exception(
              'Argument 1 must be an instance of PHPUnit2_Framework_TestResult.'
            );
        }

        $this->setUp();
        $this->basicRun($result);
        $this->tearDown();

        return $result;
    }

    // }}}
    // {{{ protected function setUp()

    /**
    * Sets up the fixture. Override to set up additional fixture
    * state.
    *
    * @access protected
    */
    protected function setUp() {}

    // }}}
    // {{{ protected function tearDown()

    /**
    * Tears down the fixture. Override to tear down the additional
    * fixture state.
    *
    * @access protected
    */
    protected function tearDown() {}

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
