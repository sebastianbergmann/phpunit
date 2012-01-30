<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'AbstractMockTestClass.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'AnInterface.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'FunctionCallback.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MethodCallback.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'PartialMockTestClass.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'SomeClass.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'StaticMockTestClass.php';

/**
 *
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author     Patrick Mueller <elias0@gmx.net>
 * @author     Frank Kleine <mikey@stubbles.net>
 * @copyright  2010-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Framework_MockObjectTest extends PHPUnit_Framework_TestCase
{
    public function testMockedMethodIsNeverCalled()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->never())
             ->method('doSomething');
    }

    public function testMockedMethodIsNotCalledWhenExpectsAnyWithParameter()
    {
        $mock = $this->getMock('SomeClass');
        $mock->expects($this->any())
             ->method('doSomethingElse')
             ->with('someArg');
    }

    public function testMockedMethodIsCalledAtLeastOnce()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->atLeastOnce())
             ->method('doSomething');

        $mock->doSomething();
    }

    public function testMockedMethodIsCalledAtLeastOnce2()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->atLeastOnce())
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testMockedMethodIsCalledOnce()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->once())
             ->method('doSomething');

        $mock->doSomething();
    }

    public function testMockedMethodIsCalledOnceWithParameter()
    {
        $mock = $this->getMock('SomeClass');
        $mock->expects($this->once())
             ->method('doSomethingElse')
             ->with($this->equalTo('something'));

        $mock->doSomethingElse('something');
    }

    public function testMockedMethodIsCalledExactly()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->exactly(2))
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testStubbedException()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->any())
             ->method('doSomething')
             ->will($this->throwException(new Exception));

        try {
            $mock->doSomething();
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testStubbedReturnValue()
    {
        $mock = $this->getMock('AnInterface');
        $mock->expects($this->any())
             ->method('doSomething')
             ->will($this->returnValue('something'));

        $this->assertEquals('something', $mock->doSomething());
    }

    public function testStubbedReturnValueMap()
    {
        $map = array(
            array('a', 'b', 'c', 'd'),
            array('e', 'f', 'g', 'h')
        );

        $mock = $this->getMock('AnInterface');
        $mock->expects($this->any())
             ->method('doSomething')
             ->will($this->returnValueMap($map));

        $this->assertEquals('d', $mock->doSomething('a', 'b', 'c'));
        $this->assertEquals('h', $mock->doSomething('e', 'f', 'g'));
        $this->assertEquals(NULL, $mock->doSomething('foo', 'bar'));
    }

    public function testFunctionCallback()
    {
        $mock = $this->getMock('SomeClass', array('doSomething'), array(), '', FALSE);
        $mock->expects($this->once())
             ->method('doSomething')
             ->will($this->returnCallback('functionCallback'));

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));
    }

    public function testStaticMethodCallback()
    {
        $mock = $this->getMock('SomeClass', array('doSomething'), array(), '', FALSE);
        $mock->expects($this->once())
             ->method('doSomething')
             ->will($this->returnCallback(array('MethodCallback', 'staticCallback')));

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));
    }

    public function testPublicMethodCallback()
    {
        $mock = $this->getMock('SomeClass', array('doSomething'), array(), '', FALSE);
        $mock->expects($this->once())
             ->method('doSomething')
             ->will($this->returnCallback(array(new MethodCallback, 'nonStaticCallback')));

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));
    }

    public function testMockClassOnlyGeneratedOnce()
    {
        $mock1 = $this->getMock('AnInterface');
        $mock2 = $this->getMock('AnInterface');

        $this->assertEquals(get_class($mock1), get_class($mock2));
    }

    public function testMockClassDifferentForPartialMocks()
    {
        $mock1 = $this->getMock('PartialMockTestClass');
        $mock2 = $this->getMock('PartialMockTestClass', array('doSomething'));
        $mock3 = $this->getMock('PartialMockTestClass', array('doSomething'));
        $mock4 = $this->getMock('PartialMockTestClass', array('doAnotherThing'));
        $mock5 = $this->getMock('PartialMockTestClass', array('doAnotherThing'));

        $this->assertNotEquals(get_class($mock1), get_class($mock2));
        $this->assertNotEquals(get_class($mock1), get_class($mock3));
        $this->assertNotEquals(get_class($mock1), get_class($mock4));
        $this->assertNotEquals(get_class($mock1), get_class($mock5));
        $this->assertEquals(get_class($mock2), get_class($mock3));
        $this->assertNotEquals(get_class($mock2), get_class($mock4));
        $this->assertNotEquals(get_class($mock2), get_class($mock5));
        $this->assertEquals(get_class($mock4), get_class($mock5));
    }

    public function testMockClassStoreOverrulable()
    {
        $mock1 = $this->getMock('PartialMockTestClass');
        $mock2 = $this->getMock('PartialMockTestClass', array(), array(), 'MyMockClassNameForPartialMockTestClass1');
        $mock3 = $this->getMock('PartialMockTestClass');
        $mock4 = $this->getMock('PartialMockTestClass', array('doSomething'), array(), 'AnotherMockClassNameForPartialMockTestClass');
        $mock5 = $this->getMock('PartialMockTestClass', array(), array(), 'MyMockClassNameForPartialMockTestClass2');

        $this->assertNotEquals(get_class($mock1), get_class($mock2));
        $this->assertEquals(get_class($mock1), get_class($mock3));
        $this->assertNotEquals(get_class($mock1), get_class($mock4));
        $this->assertNotEquals(get_class($mock2), get_class($mock3));
        $this->assertNotEquals(get_class($mock2), get_class($mock4));
        $this->assertNotEquals(get_class($mock2), get_class($mock5));
        $this->assertNotEquals(get_class($mock3), get_class($mock4));
        $this->assertNotEquals(get_class($mock3), get_class($mock5));
        $this->assertNotEquals(get_class($mock4), get_class($mock5));
    }

    /**
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testMockClassStoreOverruleSameClassNameThrowsException()
    {
        $mock1 = $this->getMock('PartialMockTestClass', array(), array(), __FUNCTION__);
        $mock2 = $this->getMock('PartialMockTestClass', array(), array(), __FUNCTION__);
    }

    public function testOriginalConstructorSettingConsidered()
    {
        $mock1 = $this->getMock('PartialMockTestClass');
        $mock2 = $this->getMock('PartialMockTestClass', array(), array(), '', FALSE);

        $this->assertTrue($mock1->constructorCalled);
        $this->assertFalse($mock2->constructorCalled);
    }

    public function testOriginalCloneSettingConsidered()
    {
        $mock1 = $this->getMock('PartialMockTestClass');
        $mock2 = $this->getMock('PartialMockTestClass', array(), array(), '', TRUE, FALSE);

        $this->assertNotEquals(get_class($mock1), get_class($mock2));
    }

    public function testStubbedReturnValueForStaticMethod()
    {
        $this->getMockClass(
          'StaticMockTestClass',
          array('doSomething'),
          array(),
          'StaticMockTestClassMock'
        );

        StaticMockTestClassMock::staticExpects($this->any())
          ->method('doSomething')
          ->will($this->returnValue('something'));

        $this->assertEquals(
          'something', StaticMockTestClassMock::doSomething()
        );
    }

    public function testStubbedReturnValueForStaticMethod2()
    {
        $this->getMockClass(
          'StaticMockTestClass',
          array('doSomething'),
          array(),
          'StaticMockTestClassMock2'
        );

        StaticMockTestClassMock2::staticExpects($this->any())
          ->method('doSomething')
          ->will($this->returnValue('something'));

        $this->assertEquals(
          'something', StaticMockTestClassMock2::doSomethingElse()
        );
    }

    public function testGetMockForAbstractClass()
    {
        $mock = $this->getMock('AbstractMockTestClass');
        $mock->expects($this->never())
             ->method('doSomething');
    }

    public function testStaticMethodCallCloneObjectParametersByDefault()
    {
        $expectedObject = new StdClass;

        $this->getMockClass(
          'StaticMockTestClass',
          array('doSomething'),
          array(),
          'StaticMockTestClassMock3'
        );

        $actualArguments = array();

        StaticMockTestClassMock3::staticExpects($this->any())
        ->method('doSomething')
        ->will($this->returnCallback(function() use (&$actualArguments) {
            $actualArguments = func_get_args();
        }));

        StaticMockTestClassMock3::doSomething($expectedObject);

        $this->assertEquals(1, count($actualArguments));
        $this->assertNotSame($expectedObject, $actualArguments[0]);
    }

    public function testStaticMethodCallCloneObjectParametersIfCloneObjectSetTrue()
    {
        $cloneObjects = TRUE;
        $expectedObject = new StdClass;

        $this->getMockClass(
          'StaticMockTestClass',
          array('doSomething'),
          array(),
          'StaticMockTestClassMock4',
          FALSE,
          TRUE,
          TRUE,
          $cloneObjects
        );

        $actualArguments = array();

        StaticMockTestClassMock4::staticExpects($this->any())
        ->method('doSomething')
        ->will($this->returnCallback(function() use (&$actualArguments) {
            $actualArguments = func_get_args();
        }));

        StaticMockTestClassMock4::doSomething($expectedObject);

        $this->assertEquals(1, count($actualArguments));
        $this->assertEquals($expectedObject, $actualArguments[0]);
        $this->assertNotSame($expectedObject, $actualArguments[0]);
    }

    public function testObjectMethodCallCloneObjectParametersByDefault()
    {
        $expectedObject = new StdClass;

        $mock = $this->getMock('SomeClass', array('doSomethingElse'));

        $actualArguments = array();

        $mock->expects($this->any())
        ->method('doSomethingElse')
        ->will($this->returnCallback(function() use (&$actualArguments) {
            $actualArguments = func_get_args();
        }));

        $mock->doSomethingElse($expectedObject);

        $this->assertEquals(1, count($actualArguments));
        $this->assertNotSame($expectedObject, $actualArguments[0]);
    }

    public function testObjectMethodCallCloneObjectParametersIfCloneObjectSetTrue()
    {
        $cloneObjects = TRUE;
        $expectedObject = new StdClass;

        $mock = $this->getMock('SomeClass', array('doSomethingElse'), array(), '', TRUE, TRUE, TRUE, $cloneObjects);

        $actualArguments = array();

        $mock->expects($this->any())
        ->method('doSomethingElse')
        ->will($this->returnCallback(function() use (&$actualArguments) {
            $actualArguments = func_get_args();
        }));

        $mock->doSomethingElse($expectedObject);

        $this->assertEquals(1, count($actualArguments));
        $this->assertEquals($expectedObject, $actualArguments[0]);
        $this->assertNotSame($expectedObject, $actualArguments[0]);
    }
}
