<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.6
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ExceptionTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ExceptionNamespaceTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'RequirementsTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'RequirementsClassDocBlockTest.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.6
 */
class Util_TestTest extends PHPUnit_Framework_TestCase
{
    public function testGetExpectedException()
    {
        $this->assertSame(
          array('class' => 'FooBarBaz', 'code' => NULL, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testOne')
        );

        $this->assertSame(
          array('class' => 'Foo_Bar_Baz', 'code' => NULL, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testTwo')
        );

        $this->assertSame(
          array('class' => 'Foo\Bar\Baz', 'code' => NULL, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testThree')
        );

        $this->assertSame(
          array('class' => 'ほげ', 'code' => NULL, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testFour')
        );

        $this->assertSame(
          array('class' => 'Class', 'code' => 1234, 'message' => 'Message'),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testFive')
        );

        $this->assertSame(
          array('class' => 'Class', 'code' => 1234, 'message' => 'Message'),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testSix')
        );

        $this->assertSame(
          array('class' => 'Class', 'code' => 'ExceptionCode', 'message' => 'Message'),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testSeven')
        );

        $this->assertSame(
          array('class' => 'Class', 'code' => 0, 'message' => 'Message'),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testEight')
        );
        $this->assertSame(
          array('class' => 'Class', 'code' => ExceptionTest::ERROR_CODE, 'message' => ExceptionTest::ERROR_MESSAGE),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testNine')
        );
        $this->assertSame(
          array('class' => 'Class', 'code' => NULL, 'message' => ''),
          PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testSingleLine')
        );
        $this->assertSame(
            array('class' => 'Class', 'code' => My\Space\ExceptionNamespaceTest::ERROR_CODE, 'message' => My\Space\ExceptionNamespaceTest::ERROR_MESSAGE),
            PHPUnit_Util_Test::getExpectedException('My\Space\ExceptionNamespaceTest', 'testConstants')
        );
        // Ensure the Class::CONST expression is only evaluated when the constant really exists
        $this->assertSame(
            array('class' => 'Class', 'code' => 'ExceptionTest::UNKNOWN_CODE_CONSTANT', 'message' => 'ExceptionTest::UNKNOWN_MESSAGE_CONSTANT'),
            PHPUnit_Util_Test::getExpectedException('ExceptionTest', 'testUnknownConstants')
        );
        $this->assertSame(
            array('class' => 'Class', 'code' => 'My\Space\ExceptionNamespaceTest::UNKNOWN_CODE_CONSTANT', 'message' => 'My\Space\ExceptionNamespaceTest::UNKNOWN_MESSAGE_CONSTANT'),
            PHPUnit_Util_Test::getExpectedException('My\Space\ExceptionNamespaceTest', 'testUnknownConstants')
        );
    }

    public function provideRequirements()
    {
        return array(
            array('testOne',   array()),
            array('testTwo',   array('PHPUnit' => '1.0')),
            array('testThree', array('PHP' => '2.0')),
            array('testFour',  array('PHPUnit'=>'2.0', 'PHP' => '1.0')),
            array('testFive',  array('PHP' => '5.4.0RC6')),
            array('testSix',   array('PHP' => '5.4.0-alpha1')),
            array('testSeven', array('PHP' => '5.4.0beta2')),
            array('testEight', array('PHP' => '5.4-dev')),
            array('testNine',  array('functions' => array('testFunc'))),
            array('testTen',   array('extensions' => array('testExt'))),
            array(
                'testAllPossibleRequirements',
                array(
                    'PHP' => '99-dev',
                    'PHPUnit' => '9-dev',
                    'functions' => array(
                        'testFuncOne',
                        'testFuncTwo',
                    ),
                    'extensions' => array(
                        'testExtOne',
                        'testExtTwo',
                    )
                )
            )
        );
    }

    /**
     * @dataProvider provideRequirements
     */
    public function testGetRequirements($test, $result)
    {
        $this->assertEquals(
          $result,
          PHPUnit_Util_Test::getRequirements('RequirementsTest', $test)
        );
    }

    public function testGetRequirementsMergesClassAndMethodDocBlocks()
    {
        $expectedAnnotations = array(
            'PHP' => '5.4',
            'PHPUnit' => '3.7',
            'functions' => array(
                'testFuncClass',
                'testFuncMethod',
            ),
            'extensions' => array(
                'testExtClass',
                'testExtMethod',
            )
        );

        $this->assertEquals(
          $expectedAnnotations,
          PHPUnit_Util_Test::getRequirements('RequirementsClassDocBlockTest', 'testMethod')
        );
    }

    public function testGetProvidedDataRegEx()
    {
        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('class::method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\class::method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider namespace\namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\namespace\class::method', $matches[1]);

        $result = preg_match(PHPUnit_Util_Test::REGEX_DATA_PROVIDER, '@dataProvider メソッド', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('メソッド', $matches[1]);
    }

    public function testParseAnnotation()
    {
        $this->assertEquals(
          array('Foo', 'ほげ'),
          PHPUnit_Util_Test::getDependencies(get_class($this), 'methodForTestParseAnnotation')
        );
    }

    /**
     * @depends Foo
     * @depends ほげ
     */
    public function methodForTestParseAnnotation()
    {
    }

    public function testParseAnnotationThatIsOnlyOneLine()
    {
        $this->assertEquals(
          array('Bar'),
          PHPUnit_Util_Test::getDependencies(get_class($this), 'methodForTestParseAnnotationThatIsOnlyOneLine')
        );
    }

    /** @depends Bar */
    public function methodForTestParseAnnotationThatIsOnlyOneLine()
    {
    }
}
