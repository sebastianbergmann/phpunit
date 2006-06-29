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
 * @version    CVS: $Id: NamePrettifierTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'PHPUnit2/Util/TestDox/NamePrettifier.php';

/**
 * 
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.1.0
 */
class Util_TestDox_NamePrettifierTest extends PHPUnit2_Framework_TestCase {
    private $namePrettifier;

    protected function setUp() {
        $this->namePrettifier = new PHPUnit2_Util_TestDox_NamePrettifier;
    }

    public function testTitleHasSensibleDefaults() {
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('FooTest'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('TestFoo'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('TestFooTest'));
    }

    public function testCaterForUserDefinedSuffix() {
        $this->namePrettifier->setSuffix('TestCase');
        $this->namePrettifier->setPrefix(NULL);

        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('FooTestCase'));
        $this->assertEquals('TestFoo', $this->namePrettifier->prettifyTestClass('TestFoo'));
        $this->assertEquals('FooTest', $this->namePrettifier->prettifyTestClass('FooTest'));
    }

    public function testCaterForUserDefinedPrefix() {
        $this->namePrettifier->setSuffix(NULL);
        $this->namePrettifier->setPrefix('XXX');

        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('XXXFoo'));
        $this->assertEquals('TestXXX', $this->namePrettifier->prettifyTestClass('TestXXX'));
        $this->assertEquals('XXX', $this->namePrettifier->prettifyTestClass('XXXXXX'));
    }

    public function testTestNameIsConvertedToASentence() {
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('testThisIsATest'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('testThisIsATest2'));
        $this->assertEquals('This2 is a test', $this->namePrettifier->prettifyTestMethod('testThis2IsATest'));
        $this->assertEquals('database_column_spec is set correctly', $this->namePrettifier->prettifyTestMethod('testdatabase_column_specIsSetCorrectly'));
    }

    public function testIsATestIsFalseForNonTestMethods() {
        $this->assertFalse($this->namePrettifier->isATestMethod('setUp'));
        $this->assertFalse($this->namePrettifier->isATestMethod('tearDown'));
        $this->assertFalse($this->namePrettifier->isATestMethod('foo'));
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
