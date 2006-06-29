<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: TestSetup.php 539 2006-02-13 16:08:42Z sb $
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
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
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
