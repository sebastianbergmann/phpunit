<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Extensions/TestDecorator.php';

/**
 * A Decorator to set up and tear down additional fixture state.
 * Subclass TestSetup and insert it into your tests when you want
 * to set up additional state once before the tests are run.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Extensions_TestSetup extends PHPUnit2_Extensions_TestDecorator {
    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
     *
     * @param  PHPUnit2_Framework_TestResult $result
     * @return PHPUnit2_Framework_TestResult
     * @throws Exception
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
        $this->copyFixtureToTest();
        $this->basicRun($result);
        $this->tearDown();

        return $result;
    }

    /**
     * Copies the fixture set up by setUp() to the test.
     *
     * @access private
     * @since  Method available since Release 2.3.0
     */
    private function copyFixtureToTest() {
        $object = new ReflectionClass($this);

        foreach ($object->getProperties() as $property) {
            $name = $property->getName();

            if ($name != 'test') {
                $this->doCopyFixtureToTest($this->test, $name, $this->$name);
            }
        }
    }

    /**
     * @access private
     * @since  Method available since Release 2.3.0
     */
    private function doCopyFixtureToTest($object, $name, &$value) {
        if ($object instanceof PHPUnit2_Framework_TestSuite) {
            foreach ($object->tests() as $test) {
                $this->doCopyFixtureToTest($test, $name, $value);
            }
        } else {
            $object->$name =& $value;
        }
    }

    /**
     * Sets up the fixture. Override to set up additional fixture
     * state.
     *
     * @access protected
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture. Override to tear down the additional
     * fixture state.
     *
     * @access protected
     */
    protected function tearDown() {
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
