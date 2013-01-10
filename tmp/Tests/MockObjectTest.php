<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2010-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'AbstractMockTestClass.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'AbstractTrait.php';
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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Patrick Mueller <elias0@gmx.net>
 * @author     Frank Kleine <mikey@stubbles.net>
 * @copyright  2010-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testGetMockWithFixedClassNameCanProduceTheSameMockTwice()
    {
        $mock = $this->getMockBuilder('StdClass')->setMockClassName('FixedName')->getMock();
        $mock = $this->getMockBuilder('StdClass')->setMockClassName('FixedName')->getMock();
        $this->assertInstanceOf('StdClass', $mock);
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

    public function testGetMockForTrait()
    {
        $mock = $this->getMockForTrait('AbstractTrait');
        $mock->expects($this->never())->method('doSomething');

        $parent = get_parent_class($mock);
        $traits = class_uses($parent, FALSE);

        $this->assertContains('AbstractTrait', $traits);
    }

    public function testClonedMockObjectShouldStillEqualTheOriginal()
    {
        $a = $this->getMock('stdClass');
        $b = clone $a;
        $this->assertEquals($a, $b);
    }

    public function testMockObjectsConstructedIndepentantlyShouldBeEqual()
    {
        $a = $this->getMock('stdClass');
        $b = $this->getMock('stdClass');
        $this->assertEquals($a, $b);
    }

    public function testMockObjectsConstructedIndepentantlyShouldNotBeTheSame()
    {
        $a = $this->getMock('stdClass');
        $b = $this->getMock('stdClass');
        $this->assertNotSame($a, $b);
    }

    public function testClonedMockObjectCanBeUsedInPlaceOfOriginalOne()
    {
        $x = $this->getMock('stdClass');
        $y = clone $x;

        $mock = $this->getMock('stdClass', array('foo'));
        $mock->expects($this->once())->method('foo')->with($this->equalTo($x));
        $mock->foo($y);
    }

    public function testClonedMockObjectIsNotIdenticalToOriginalOne()
    {
        $x = $this->getMock('stdClass');
        $y = clone $x;

        $mock = $this->getMock('stdClass', array('foo'));
        $mock->expects($this->once())->method('foo')->with($this->logicalNot($this->identicalTo($x)));
        $mock->foo($y);
    }

    public function testStaticMethodCallWithArgumentCloningEnabled()
    {
        $expectedObject = new StdClass;

        $this->getMockClass(
          'StaticMockTestClass',
          array('doSomething'),
          array(),
          'StaticMockTestClassMock3',
          FALSE,
          TRUE,
          TRUE,
          TRUE
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

    public function testStaticMethodCallWithArgumentCloningDisabled()
    {
        $expectedObject = new StdClass;

        $this->getMockClass(
          'StaticMockTestClass',
          array('doSomething'),
          array(),
          'StaticMockTestClassMock4',
          FALSE,
          TRUE,
          TRUE,
          FALSE
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
        $this->assertSame($expectedObject, $actualArguments[0]);
    }

    public function testObjectMethodCallWithArgumentCloningEnabled()
    {
        $expectedObject = new StdClass;

        $mock = $this->getMockBuilder('SomeClass')
                     ->setMethods(array('doSomethingElse'))
                     ->enableArgumentCloning()
                     ->getMock();

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

    public function testObjectMethodCallWithArgumentCloningDisabled()
    {
        $expectedObject = new StdClass;

        $mock = $this->getMockBuilder('SomeClass')
                     ->setMethods(array('doSomethingElse'))
                     ->disableArgumentCloning()
                     ->getMock();

        $actualArguments = array();

        $mock->expects($this->any())
        ->method('doSomethingElse')
        ->will($this->returnCallback(function() use (&$actualArguments) {
            $actualArguments = func_get_args();
        }));

        $mock->doSomethingElse($expectedObject);

        $this->assertEquals(1, count($actualArguments));
        $this->assertSame($expectedObject, $actualArguments[0]);
    }

    public function testArgumentCloningOptionGeneratesUniqueMock()
    {
        $mockWithCloning = $this->getMockBuilder('SomeClass')
                                ->setMethods(array('doSomethingElse'))
                                ->enableArgumentCloning()
                                ->getMock();

        $mockWithoutCloning = $this->getMockBuilder('SomeClass')
                                   ->setMethods(array('doSomethingElse'))
                                   ->disableArgumentCloning()
                                   ->getMock();

        $this->assertNotEquals($mockWithCloning, $mockWithoutCloning);
    }

    public function testVerificationOfMethodNameFailsWithoutParameters()
    {
        $mock = $this->getMock('SomeClass', array('right', 'wrong'), array(), '', TRUE, TRUE, TRUE);
        $mock->expects($this->once())
             ->method('right');

        $mock->wrong();
        try {
            $mock->__phpunit_verify();
            $this->fail('Expected exception');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is equal to <string:right> when invoked 1 time(s).\n"
                . "Method was expected to be called 1 times, actually called 0 times.\n",
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfMethodNameFailsWithParameters()
    {
        $mock = $this->getMock('SomeClass', array('right', 'wrong'), array(), '', TRUE, TRUE, TRUE);
        $mock->expects($this->once())
             ->method('right');

        $mock->wrong();
        try {
            $mock->__phpunit_verify();
            $this->fail('Expected exception');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is equal to <string:right> when invoked 1 time(s).\n"
                . "Method was expected to be called 1 times, actually called 0 times.\n",
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfMethodNameFailsWithWrongParameters()
    {
        $mock = $this->getMock('SomeClass', array('right', 'wrong'), array(), '', TRUE, TRUE, TRUE);
        $mock->expects($this->once())
             ->method('right')
             ->with(array('first', 'second'));

        try {
            $mock->right(array('second'));
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is equal to <string:right> when invoked 1 time(s)\n"
                . "Parameter 0 for invocation SomeClass::right(Array (...)) does not match expected value.\n"
                . "Failed asserting that two arrays are equal.",
                $e->getMessage()
            );
        }

        try {
            $mock->__phpunit_verify();
            $this->fail('Expected exception');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is equal to <string:right> when invoked 1 time(s).\n"
                . "Parameter 0 for invocation SomeClass::right(Array (...)) does not match expected value.\n"
                . "Failed asserting that two arrays are equal.\n"
                . "--- Expected\n"
                . "+++ Actual\n"
                . "@@ @@\n"
                . " Array (\n"
                . "-    0 => 'first'\n"
                . "-    1 => 'second'\n"
                . "+    0 => 'second'\n"
                . " )\n",
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfNeverFailsWithEmptyParameters()
    {
        $mock = $this->getMock('SomeClass', array('right', 'wrong'), array(), '', TRUE, TRUE, TRUE);
        $mock->expects($this->never())
             ->method('right')
             ->with();

        try {
            $mock->right();
            $this->fail('Expected exception');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertSame(
                'SomeClass::right() was not expected to be called.',
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfNeverFailsWithAnyParameters()
    {
        $mock = $this->getMock('SomeClass', array('right', 'wrong'), array(), '', TRUE, TRUE, TRUE);
        $mock->expects($this->never())
             ->method('right')
             ->withAnyParameters();

        try {
            $mock->right();
            $this->fail('Expected exception');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertSame(
                'SomeClass::right() was not expected to be called.',
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    /**
     * @requires extension soap
     */
    public function testCreateMockFromWsdl()
    {
        $mock = $this->getMockFromWsdl(__DIR__ . '/_files/GoogleSearch.wsdl', 'WsdlMock');
        $this->assertStringStartsWith(
            'Mock_WsdlMock_',
            get_class($mock)
        );
    }

    /**
     * @requires extension soap
     */
    public function testCreateNamespacedMockFromWsdl()
    {
        $mock = $this->getMockFromWsdl(__DIR__ . '/_files/GoogleSearch.wsdl', 'My\\Space\\WsdlMock');
        $this->assertStringStartsWith(
            'Mock_WsdlMock_',
            get_class($mock)
        );
    }

    /**
     * @requires extension soap
     */
    public function testCreateTwoMocksOfOneWsdlFile()
    {
        $mock = $this->getMockFromWsdl(__DIR__ . '/_files/GoogleSearch.wsdl');
        $mock = $this->getMockFromWsdl(__DIR__ . '/_files/GoogleSearch.wsdl');
    }

    private function resetMockObjects()
    {
        $refl = new ReflectionObject($this);
        $refl = $refl->getParentClass();
        $prop = $refl->getProperty('mockObjects');
        $prop->setAccessible(true);
        $prop->setValue($this, array());
    }
}
