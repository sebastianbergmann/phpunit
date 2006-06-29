<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2 :: TestDox                                            |
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
// $Id: NamePrettifierTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'PHPUnit2/Extensions/TestDox/NamePrettifier.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2_Extensions_TestDox
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Extensions_TestDox_NamePrettifierTest extends PHPUnit2_Framework_TestCase {
    private $namePrettifier;

    protected function setUp() {
        $this->namePrettifier = new PHPUnit2_Extensions_TestDox_NamePrettifier;
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
        $this->assertEquals('database_column_spec is set correctly', $this->namePrettifier->prettifyTestMethod('testdatabase_column_specIsSetCorrectly'));
    }

    public function testIsATestIsFalseForNonTestMethods() {
        $this->assertFalse($this->namePrettifier->isATestMethod('setUp'));
        $this->assertFalse($this->namePrettifier->isATestMethod('tearDown'));
        $this->assertFalse($this->namePrettifier->isATestMethod('foo'));
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
