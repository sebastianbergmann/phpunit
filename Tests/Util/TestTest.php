<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.6
 */

if (!defined('TEST_FILES_PATH')) {
    define(
      'TEST_FILES_PATH',
      dirname(__DIR__) . DIRECTORY_SEPARATOR .
      '_files' . DIRECTORY_SEPARATOR
    );
}

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
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
            array('testOne',    array()),
            array('testTwo',    array('PHPUnit' => '1.0')),
            array('testThree',  array('PHP' => '2.0')),
            array('testFour',   array('PHPUnit'=>'2.0', 'PHP' => '1.0')),
            array('testFive',   array('PHP' => '5.4.0RC6')),
            array('testSix',    array('PHP' => '5.4.0-alpha1')),
            array('testSeven',  array('PHP' => '5.4.0beta2')),
            array('testEight',  array('PHP' => '5.4-dev')),
            array('testNine',   array('functions' => array('testFunc'))),
            array('testTen',    array('extensions' => array('testExt'))),
            array('testEleven', array('OS' => '/Linux/i')),
            array(
                'testAllPossibleRequirements',
                array(
                    'PHP' => '99-dev',
                    'PHPUnit' => '9-dev',
                    'OS' => '/DOESNOTEXIST/i',
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
            'OS' => '/WINNT/i',
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

    /**
     * @covers       PHPUnit_Util_Test::getLinesToBeCovered
     * @covers       PHPUnit_Util_Test::resolveCoversToReflectionObjects
     * @dataProvider getLinesToBeCoveredProvider
     */
    public function testGetLinesToBeCovered($test, $lines)
    {
        if (strpos($test, 'Namespace') === 0) {
            $expected = array(
              TEST_FILES_PATH . 'NamespaceCoveredClass.php' => $lines
            );
        }

        else if ($test === 'CoverageNoneTest') {
            $expected = array();
        }

        else if ($test === 'CoverageNothingTest') {
            $expected = false;
        }

        else if ($test === 'CoverageFunctionTest') {
            $expected = array(
              TEST_FILES_PATH . 'CoveredFunction.php' => $lines
            );
        }

        else {
            $expected = array(TEST_FILES_PATH . 'CoveredClass.php' => $lines);
        }

        $this->assertEquals(
          $expected,
          PHPUnit_Util_Test::getLinesToBeCovered(
            $test, 'testSomething'
          )
        );
    }

    /**
     * @covers            PHPUnit_Util_Test::getLinesToBeCovered
     * @covers            PHPUnit_Util_Test::resolveCoversToReflectionObjects
     * @expectedException PHPUnit_Framework_CodeCoverageException
     */
    public function testGetLinesToBeCovered2()
    {
        PHPUnit_Util_Test::getLinesToBeCovered(
          'NotExistingCoveredElementTest', 'testOne'
        );
    }

    /**
     * @covers            PHPUnit_Util_Test::getLinesToBeCovered
     * @covers            PHPUnit_Util_Test::resolveCoversToReflectionObjects
     * @expectedException PHPUnit_Framework_CodeCoverageException
     */
    public function testGetLinesToBeCovered3()
    {
        PHPUnit_Util_Test::getLinesToBeCovered(
          'NotExistingCoveredElementTest', 'testTwo'
        );
    }

    /**
     * @covers            PHPUnit_Util_Test::getLinesToBeCovered
     * @covers            PHPUnit_Util_Test::resolveCoversToReflectionObjects
     * @expectedException PHPUnit_Framework_CodeCoverageException
     */
    public function testGetLinesToBeCovered4()
    {
        PHPUnit_Util_Test::getLinesToBeCovered(
          'NotExistingCoveredElementTest', 'testThree'
        );
    }

    /**
     * @covers PHPUnit_Util_Test::getLinesToBeCovered
     */
    public function testGetLinesToBeCoveredSkipsNonExistantMethods()
    {
        $this->assertSame(
          array(),
          PHPUnit_Util_Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'methodDoesNotExist'
          )
        );
    }

    /**
     * @covers PHPUnit_Util_Test::getLinesToBeCovered
     * @expectedException PHPUnit_Framework_CodeCoverageException
     */
    public function testTwoCoversDefaultClassAnnoationsAreNotAllowed()
    {
        PHPUnit_Util_Test::getLinesToBeCovered(
          'CoverageTwoDefaultClassAnnotations',
          'testSomething'
        );
    }

    /**
     * @covers PHPUnit_Util_Test::getLinesToBeCovered
     */
    public function testFunctionParenthesesAreAllowed()
    {
        $this->assertSame(
          array(TEST_FILES_PATH . 'CoveredFunction.php' => range(2, 4)),
          PHPUnit_Util_Test::getLinesToBeCovered(
            'CoverageFunctionParenthesesTest',
            'testSomething'
          )
        );
    }

    /**
     * @covers PHPUnit_Util_Test::getLinesToBeCovered
     */
    public function testFunctionParenthesesAreAllowedWithWhitespace()
    {
        $this->assertSame(
          array(TEST_FILES_PATH . 'CoveredFunction.php' => range(2, 4)),
          PHPUnit_Util_Test::getLinesToBeCovered(
            'CoverageFunctionParenthesesWhitespaceTest',
            'testSomething'
          )
        );
    }

    /**
     * @covers PHPUnit_Util_Test::getLinesToBeCovered
     */
    public function testMethodParenthesesAreAllowed()
    {
        $this->assertSame(
          array(TEST_FILES_PATH . 'CoveredClass.php' => range(31, 35)),
          PHPUnit_Util_Test::getLinesToBeCovered(
            'CoverageMethodParenthesesTest',
            'testSomething'
          )
        );
    }

    /**
     * @covers PHPUnit_Util_Test::getLinesToBeCovered
     */
    public function testMethodParenthesesAreAllowedWithWhitespace()
    {
        $this->assertSame(
          array(TEST_FILES_PATH . 'CoveredClass.php' => range(31, 35)),
          PHPUnit_Util_Test::getLinesToBeCovered(
            'CoverageMethodParenthesesWhitespaceTest',
            'testSomething'
          )
        );
    }

    public function getLinesToBeCoveredProvider()
    {
        return array(
          array(
            'CoverageNoneTest',
            array()
          ),
          array(
            'CoverageClassExtendedTest',
            array_merge(range(19, 36), range(2, 17))
          ),
          array(
            'CoverageClassTest',
            range(19, 36)
          ),
          array(
            'CoverageMethodTest',
            range(31, 35)
          ),
          array(
            'CoverageMethodOneLineAnnotationTest',
            range(31, 35)
          ),
          array(
            'CoverageNotPrivateTest',
            array_merge(range(25, 29), range(31, 35))
          ),
          array(
            'CoverageNotProtectedTest',
            array_merge(range(21, 23), range(31, 35))
          ),
          array(
            'CoverageNotPublicTest',
            array_merge(range(21, 23), range(25, 29))
          ),
          array(
            'CoveragePrivateTest',
            range(21, 23)
          ),
          array(
            'CoverageProtectedTest',
            range(25, 29)
          ),
          array(
            'CoveragePublicTest',
            range(31, 35)
          ),
          array(
            'CoverageFunctionTest',
            range(2, 4)
          ),
          array(
            'NamespaceCoverageClassExtendedTest',
            array_merge(range(21, 38), range(4, 19))
          ),
          array(
            'NamespaceCoverageClassTest',
            range(21, 38)
          ),
          array(
            'NamespaceCoverageMethodTest',
            range(33, 37)
          ),
          array(
            'NamespaceCoverageNotPrivateTest',
            array_merge(range(27, 31), range(33, 37))
          ),
          array(
            'NamespaceCoverageNotProtectedTest',
            array_merge(range(23, 25), range(33, 37))
          ),
          array(
            'NamespaceCoverageNotPublicTest',
            array_merge(range(23, 25), range(27, 31))
          ),
          array(
            'NamespaceCoveragePrivateTest',
            range(23, 25)
          ),
          array(
            'NamespaceCoverageProtectedTest',
            range(27, 31)
          ),
          array(
            'NamespaceCoveragePublicTest',
            range(33, 37)
          ),
          array(
            'NamespaceCoverageCoversClassTest',
            array_merge(range(23, 25), range(27, 31), range(33, 37), range(6, 8), range(10, 13), range(15, 18))
          ),
          array(
            'NamespaceCoverageCoversClassPublicTest',
            range(33, 37)
          ),
          array(
            'CoverageNothingTest',
            false
          )
        );
    }
}
