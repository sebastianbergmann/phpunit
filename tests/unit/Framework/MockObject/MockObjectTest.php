<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\AbstractTrait;
use PHPUnit\TestFixture\AnInterface;
use PHPUnit\TestFixture\ClassThatImplementsSerializable;
use PHPUnit\TestFixture\ClassWithAllPossibleReturnTypes;
use PHPUnit\TestFixture\ClassWithSelfTypeHint;
use PHPUnit\TestFixture\ClassWithStaticMethod;
use PHPUnit\TestFixture\ClassWithStaticReturnTypes;
use PHPUnit\TestFixture\ClassWithUnionReturnTypes;
use PHPUnit\TestFixture\ExampleTrait;
use PHPUnit\TestFixture\InterfaceWithMethodsThatDeclareBooleanReturnTypes;
use PHPUnit\TestFixture\InterfaceWithStaticMethod;
use PHPUnit\TestFixture\MethodCallback;
use PHPUnit\TestFixture\MethodCallbackByReference;
use PHPUnit\TestFixture\MockObject\AbstractMockTestClass;
use PHPUnit\TestFixture\PartialMockTestClass;
use PHPUnit\TestFixture\SomeClass;
use PHPUnit\TestFixture\StringableClass;
use PHPUnit\TestFixture\TraitWithConstructor;
use PHPUnit\TestFixture\TraversableMockTestInterface;

/**
 * @small
 */
final class MockObjectTest extends TestCase
{
    public function testMockedMethodIsNeverCalled(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->never())
             ->method('doSomething');
    }

    public function testMockedMethodIsNeverCalledWithParameter(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->getMock();

        $mock->expects($this->never())
             ->method('doSomething')
             ->with('someArg');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testMockedMethodIsNotCalledWhenExpectsAnyWithParameter(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->getMock();

        $mock->method('doSomethingElse')
             ->with('someArg');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testMockedMethodIsNotCalledWhenMethodSpecifiedDirectlyWithParameter(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->getMock();

        $mock->method('doSomethingElse')
             ->with('someArg');
    }

    public function testMockedMethodIsCalledAtLeastOnce(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->atLeastOnce())
             ->method('doSomething');

        $mock->doSomething();
    }

    public function testMockedMethodIsCalledAtLeastOnce2(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->atLeastOnce())
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testMockedMethodIsCalledAtLeastTwice(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->atLeast(2))
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testMockedMethodIsCalledAtLeastTwice2(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->atLeast(2))
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
        $mock->doSomething();
    }

    public function testMockedMethodIsCalledAtMostTwice(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->atMost(2))
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testMockedMethodIsCalledAtMosttTwice2(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->atMost(2))
             ->method('doSomething');

        $mock->doSomething();
    }

    public function testMockedMethodIsCalledOnce(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->once())
             ->method('doSomething');

        $mock->doSomething();
    }

    public function testMockedMethodIsCalledOnceWithParameter(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->getMock();

        $mock->expects($this->once())
             ->method('doSomethingElse')
             ->with($this->equalTo('something'));

        $mock->doSomethingElse('something');
    }

    public function testMockedMethodIsCalledExactly(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->expects($this->exactly(2))
             ->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testStubbedException(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->will($this->throwException(new \Exception));

        $this->expectException(\Exception::class);

        $mock->doSomething();
    }

    public function testStubbedWillThrowException(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->willThrowException(new \Exception);

        $this->expectException(\Exception::class);

        $mock->doSomething();
    }

    public function testStubbedReturnValue(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->will($this->returnValue('something'));

        $this->assertEquals('something', $mock->doSomething());

        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->willReturn('something');

        $this->assertEquals('something', $mock->doSomething());
    }

    public function testStubbedReturnValueMap(): void
    {
        $map = [
            ['a', 'b', 'c', 'd'],
            ['e', 'f', 'g', 'h'],
        ];

        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->will($this->returnValueMap($map));

        $this->assertEquals('d', $mock->doSomething('a', 'b', 'c'));
        $this->assertEquals('h', $mock->doSomething('e', 'f', 'g'));
        $this->assertNull($mock->doSomething('foo', 'bar'));

        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->willReturnMap($map);

        $this->assertEquals('d', $mock->doSomething('a', 'b', 'c'));
        $this->assertEquals('h', $mock->doSomething('e', 'f', 'g'));
        $this->assertNull($mock->doSomething('foo', 'bar'));
    }

    public function testStubbedReturnArgument(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->will($this->returnArgument(1));

        $this->assertEquals('b', $mock->doSomething('a', 'b'));

        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->willReturnArgument(1);

        $this->assertEquals('b', $mock->doSomething('a', 'b'));
    }

    public function testFunctionCallback(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->onlyMethods(['doSomething'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('doSomething')
             ->willReturnCallback('PHPUnit\TestFixture\FunctionCallbackWrapper::functionCallback');

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));

        $mock = $this->getMockBuilder(SomeClass::class)
                     ->onlyMethods(['doSomething'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('doSomething')
             ->willReturnCallback('PHPUnit\TestFixture\FunctionCallbackWrapper::functionCallback');

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));
    }

    public function testStubbedReturnSelf(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->will($this->returnSelf());

        $this->assertEquals($mock, $mock->doSomething());

        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->willReturnSelf();

        $this->assertEquals($mock, $mock->doSomething());
    }

    public function testStubbedReturnOnConsecutiveCalls(): void
    {
        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->will($this->onConsecutiveCalls('a', 'b', 'c'));

        $this->assertEquals('a', $mock->doSomething());
        $this->assertEquals('b', $mock->doSomething());
        $this->assertEquals('c', $mock->doSomething());

        $mock = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock->method('doSomething')
             ->willReturnOnConsecutiveCalls('a', 'b', 'c');

        $this->assertEquals('a', $mock->doSomething());
        $this->assertEquals('b', $mock->doSomething());
        $this->assertEquals('c', $mock->doSomething());
    }

    public function testStaticMethodCallback(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->onlyMethods(['doSomething'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('doSomething')
             ->will($this->returnCallback([MethodCallback::class, 'staticCallback']));

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));
    }

    public function testPublicMethodCallback(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->onlyMethods(['doSomething'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('doSomething')
             ->will($this->returnCallback([new MethodCallback, 'nonStaticCallback']));

        $this->assertEquals('pass', $mock->doSomething('foo', 'bar'));
    }

    public function testMockClassOnlyGeneratedOnce(): void
    {
        $mock1 = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $mock2 = $this->getMockBuilder(AnInterface::class)
                     ->getMock();

        $this->assertEquals(\get_class($mock1), \get_class($mock2));
    }

    public function testMockClassDifferentForPartialMocks(): void
    {
        $mock1 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->getMock();

        $mock2 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->onlyMethods(['doSomething'])
                      ->getMock();

        $mock3 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->onlyMethods(['doSomething'])
                      ->getMock();

        $mock4 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->onlyMethods(['doAnotherThing'])
                      ->getMock();

        $mock5 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->onlyMethods(['doAnotherThing'])
                      ->getMock();

        $this->assertNotEquals(\get_class($mock1), \get_class($mock2));
        $this->assertNotEquals(\get_class($mock1), \get_class($mock3));
        $this->assertNotEquals(\get_class($mock1), \get_class($mock4));
        $this->assertNotEquals(\get_class($mock1), \get_class($mock5));
        $this->assertEquals(\get_class($mock2), \get_class($mock3));
        $this->assertNotEquals(\get_class($mock2), \get_class($mock4));
        $this->assertNotEquals(\get_class($mock2), \get_class($mock5));
        $this->assertEquals(\get_class($mock4), \get_class($mock5));
    }

    public function testMockClassStoreOverrulable(): void
    {
        $mock1 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->getMock();

        $mock2 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->setMockClassName('MyMockClassNameForPartialMockTestClass1')
                      ->getMock();

        $mock3 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->getMock();

        $mock4 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->onlyMethods(['doSomething'])
                      ->setMockClassName('AnotherMockClassNameForPartialMockTestClass')
                      ->getMock();

        $mock5 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->setMockClassName('MyMockClassNameForPartialMockTestClass2')
                      ->getMock();

        $this->assertNotEquals(\get_class($mock1), \get_class($mock2));
        $this->assertEquals(\get_class($mock1), \get_class($mock3));
        $this->assertNotEquals(\get_class($mock1), \get_class($mock4));
        $this->assertNotEquals(\get_class($mock2), \get_class($mock3));
        $this->assertNotEquals(\get_class($mock2), \get_class($mock4));
        $this->assertNotEquals(\get_class($mock2), \get_class($mock5));
        $this->assertNotEquals(\get_class($mock3), \get_class($mock4));
        $this->assertNotEquals(\get_class($mock3), \get_class($mock5));
        $this->assertNotEquals(\get_class($mock4), \get_class($mock5));
    }

    public function testGetMockWithFixedClassNameCanProduceTheSameMockTwice(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)->setMockClassName('FixedName')->getMock();
        $this->assertInstanceOf(stdClass::class, $mock);
    }

    public function testOriginalConstructorSettingConsidered(): void
    {
        $mock1 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->getMock();

        $mock2 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->assertTrue($mock1->constructorCalled);
        $this->assertFalse($mock2->constructorCalled);
    }

    public function testOriginalCloneSettingConsidered(): void
    {
        $mock1 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->getMock();

        $mock2 = $this->getMockBuilder(PartialMockTestClass::class)
                      ->disableOriginalClone()
                      ->getMock();

        $this->assertNotEquals(\get_class($mock1), \get_class($mock2));
    }

    /**
     * @testdox getMock() for abstract class
     */
    public function testGetMockForAbstractClass(): void
    {
        $mock = $this->getMockBuilder(AbstractMockTestClass::class)
                     ->getMock();

        $mock->expects($this->never())
             ->method('doSomething');
    }

    /**
     * @testdox getMock() for Traversable $_dataName
     * @dataProvider traversableProvider
     */
    public function testGetMockForTraversable($type): void
    {
        $mock = $this->getMockBuilder($type)
                     ->getMock();

        $this->assertInstanceOf(Traversable::class, $mock);
    }

    /**
     * @testdox getMockForTrait()
     */
    public function testGetMockForTrait(): void
    {
        $mock = $this->getMockForTrait(AbstractTrait::class);

        $mock->expects($this->never())
             ->method('doSomething');

        $parent = \get_parent_class($mock);
        $traits = \class_uses($parent, false);

        $this->assertContains(AbstractTrait::class, $traits);
    }

    public function testClonedMockObjectShouldStillEqualTheOriginal(): void
    {
        $a = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $b = clone $a;

        $this->assertEquals($a, $b);
    }

    public function testMockObjectsConstructedIndepentantlyShouldBeEqual(): void
    {
        $a = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $b = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $this->assertEquals($a, $b);
    }

    public function testMockObjectsConstructedIndepentantlyShouldNotBeTheSame(): void
    {
        $a = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $b = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $this->assertNotSame($a, $b);
    }

    public function testClonedMockObjectCanBeUsedInPlaceOfOriginalOne(): void
    {
        $x = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $y = clone $x;

        $mock = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['foo'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('foo')
             ->with($this->equalTo($x));

        $mock->foo($y);
    }

    public function testClonedMockObjectIsNotIdenticalToOriginalOne(): void
    {
        $x = $this->getMockBuilder(stdClass::class)
                  ->getMock();

        $y = clone $x;

        $mock = $this->getMockBuilder(stdClass::class)
                     ->addMethods(['foo'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('foo')
             ->with($this->logicalNot($this->identicalTo($x)));

        $mock->foo($y);
    }

    public function testObjectMethodCallWithArgumentCloningEnabled(): void
    {
        $expectedObject = new stdClass;

        $mock = $this->getMockBuilder(SomeClass::class)
                     ->onlyMethods(['doSomethingElse'])
                     ->enableArgumentCloning()
                     ->getMock();

        $actualArguments = [];

        $mock->method('doSomethingElse')
             ->will(
                 $this->returnCallback(
                     static function () use (&$actualArguments): void
                    {
                        $actualArguments = \func_get_args();
                    }
                 )
             );

        $mock->doSomethingElse($expectedObject);

        $this->assertCount(1, $actualArguments);
        $this->assertEquals($expectedObject, $actualArguments[0]);
        $this->assertNotSame($expectedObject, $actualArguments[0]);
    }

    public function testObjectMethodCallWithArgumentCloningDisabled(): void
    {
        $expectedObject = new stdClass;

        $mock = $this->getMockBuilder(SomeClass::class)
                     ->onlyMethods(['doSomethingElse'])
                     ->disableArgumentCloning()
                     ->getMock();

        $actualArguments = [];

        $mock->method('doSomethingElse')
             ->will(
                 $this->returnCallback(
                     static function () use (&$actualArguments): void
                    {
                        $actualArguments = \func_get_args();
                    }
                 )
             );

        $mock->doSomethingElse($expectedObject);

        $this->assertCount(1, $actualArguments);
        $this->assertSame($expectedObject, $actualArguments[0]);
    }

    public function testArgumentCloningOptionGeneratesUniqueMock(): void
    {
        $mockWithCloning = $this->getMockBuilder(SomeClass::class)
                                ->onlyMethods(['doSomethingElse'])
                                ->enableArgumentCloning()
                                ->getMock();

        $mockWithoutCloning = $this->getMockBuilder(SomeClass::class)
                                   ->onlyMethods(['doSomethingElse'])
                                   ->disableArgumentCloning()
                                   ->getMock();

        $this->assertNotEquals($mockWithCloning, $mockWithoutCloning);
    }

    public function testVerificationOfMethodNameFailsWithoutParameters(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->addMethods(['right', 'wrong'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('right');

        $mock->wrong();

        try {
            $mock->__phpunit_verify();
            $this->fail('Expected exception');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is \"right\" when invoked 1 time(s).\n" .
                'Method was expected to be called 1 times, actually called 0 times.' . "\n",
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfMethodNameFailsWithParameters(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->addMethods(['right', 'wrong'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('right');

        $mock->wrong();

        try {
            $mock->__phpunit_verify();
            $this->fail('Expected exception');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is \"right\" when invoked 1 time(s).\n" .
                'Method was expected to be called 1 times, actually called 0 times.' . "\n",
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfMethodNameFailsWithWrongParameters(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->addMethods(['right', 'wrong'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('right')
             ->with(['first', 'second']);

        try {
            $mock->right(['second']);
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is \"right\" when invoked 1 time(s)\n" .
                'Parameter 0 for invocation PHPUnit\TestFixture\SomeClass::right(Array (...)) does not match expected value.' . "\n" .
                'Failed asserting that two arrays are equal.',
                $e->getMessage()
            );
        }

        try {
            $mock->__phpunit_verify();

            // CHECKOUT THIS MORE CAREFULLY
//            $this->fail('Expected exception');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is \"right\" when invoked 1 time(s).\n" .
                'Parameter 0 for invocation PHPUnit\TestFixture\SomeClass::right(Array (...)) does not match expected value.' . "\n" .
                'Failed asserting that two arrays are equal.' . "\n" .
                '--- Expected' . "\n" .
                '+++ Actual' . "\n" .
                '@@ @@' . "\n" .
                ' Array (' . "\n" .
                '-    0 => \'first\'' . "\n" .
                '-    1 => \'second\'' . "\n" .
                '+    0 => \'second\'' . "\n" .
                ' )' . "\n",
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfNeverFailsWithEmptyParameters(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->addMethods(['right', 'wrong'])
                     ->getMock();

        $mock->expects($this->never())
             ->method('right')
             ->with();

        try {
            $mock->right();
            $this->fail('Expected exception');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                'PHPUnit\TestFixture\SomeClass::right() was not expected to be called.',
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVerificationOfNeverFailsWithAnyParameters(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->addMethods(['right', 'wrong'])
                     ->getMock();

        $mock->expects($this->never())
             ->method('right')
             ->withAnyParameters();

        try {
            $mock->right();
            $this->fail('Expected exception');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                'PHPUnit\TestFixture\SomeClass::right() was not expected to be called.',
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testWithAnythingInsteadOfWithAnyParameters(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->addMethods(['right', 'wrong'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('right')
             ->with($this->anything());

        try {
            $mock->right();
            $this->fail('Expected exception');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                "Expectation failed for method name is \"right\" when invoked 1 time(s)\n" .
                'Parameter count for invocation PHPUnit\TestFixture\SomeClass::right() is too low.' . "\n" .
                'To allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.',
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/81
     */
    public function testMockArgumentsPassedByReference(): void
    {
        $foo = $this->getMockBuilder(MethodCallbackByReference::class)
                    ->onlyMethods(['bar'])
                    ->disableOriginalConstructor()
                    ->disableArgumentCloning()
                    ->getMock();

        $foo->method('bar')
            ->will($this->returnCallback([$foo, 'callback']));

        $a = $b = $c = 0;

        $foo->bar($a, $b, $c);

        $this->assertEquals(1, $b);
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/81
     */
    public function testMockArgumentsPassedByReference2(): void
    {
        $foo = $this->getMockBuilder(MethodCallbackByReference::class)
                    ->disableOriginalConstructor()
                    ->disableArgumentCloning()
                    ->getMock();

        $foo->method('bar')
            ->will($this->returnCallback(
                static function (&$a, &$b, $c): void
                {
                    $b = 1;
                }
            ));

        $a = $b = $c = 0;

        $foo->bar($a, $b, $c);

        $this->assertEquals(1, $b);
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/116
     */
    public function testMockArgumentsPassedByReference3(): void
    {
        $foo = $this->getMockBuilder(MethodCallbackByReference::class)
                    ->onlyMethods(['bar'])
                    ->disableOriginalConstructor()
                    ->disableArgumentCloning()
                    ->getMock();

        $a = new stdClass;
        $b = $c = 0;

        $foo->method('bar')
            ->with($a, $b, $c)
            ->will($this->returnCallback([$foo, 'callback']));

        $this->assertNull($foo->bar($a, $b, $c));
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/796
     */
    public function testMockArgumentsPassedByReference4(): void
    {
        $foo = $this->getMockBuilder(MethodCallbackByReference::class)
                    ->onlyMethods(['bar'])
                    ->disableOriginalConstructor()
                    ->disableArgumentCloning()
                    ->getMock();

        $a = new stdClass;
        $b = $c = 0;

        $foo->method('bar')
            ->with($this->isInstanceOf(stdClass::class), $b, $c)
            ->will($this->returnCallback([$foo, 'callback']));

        $this->assertNull($foo->bar($a, $b, $c));
    }

    /**
     * @requires extension soap
     */
    public function testCreateMockFromWsdl(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl', 'WsdlMock');

        $this->assertStringStartsWith(
            'Mock_WsdlMock_',
            \get_class($mock)
        );
    }

    /**
     * @requires extension soap
     */
    public function testCreateNamespacedMockFromWsdl(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl', 'My\\Space\\WsdlMock');

        $this->assertStringStartsWith(
            'Mock_WsdlMock_',
            \get_class($mock)
        );
    }

    /**
     * @requires extension soap
     */
    public function testCreateTwoMocksOfOneWsdlFile(): void
    {
        $a = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl');
        $b = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl');

        $this->assertStringStartsWith('Mock_GoogleSearch_', \get_class($a));
        $this->assertEquals(\get_class($a), \get_class($b));
    }

    /**
     * @see      https://github.com/sebastianbergmann/phpunit/issues/2573
     * @ticket   2573
     * @requires extension soap
     */
    public function testCreateMockOfWsdlFileWithSpecialChars(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'Go ogle-Sea.rch.wsdl');

        $this->assertStringStartsWith('Mock_GoogleSearch_', \get_class($mock));
    }

    /**
     * @see    https://github.com/sebastianbergmann/phpunit-mock-objects/issues/156
     * @ticket 156
     */
    public function testInterfaceWithStaticMethodCanBeStubbed(): void
    {
        $this->assertInstanceOf(
            InterfaceWithStaticMethod::class,
            $this->getMockBuilder(InterfaceWithStaticMethod::class)->getMock()
        );
    }

    public function testInvokingStubbedStaticMethodRaisesException(): void
    {
        $mock = $this->getMockBuilder(ClassWithStaticMethod::class)->getMock();

        $this->expectException(\PHPUnit\Framework\MockObject\BadMethodCallException::class);

        $mock->staticMethod();
    }

    /**
     * @see    https://github.com/sebastianbergmann/phpunit-mock-objects/issues/171
     * @ticket 171
     * @requires PHP < 8.1
     */
    public function testStubForClassThatImplementsSerializableCanBeCreatedWithoutInvokingTheConstructor(): void
    {
        $this->assertInstanceOf(
            ClassThatImplementsSerializable::class,
            $this->getMockBuilder(ClassThatImplementsSerializable::class)
                 ->disableOriginalConstructor()
                 ->getMock()
        );
    }

    public function testGetMockForClassWithSelfTypeHint(): void
    {
        $this->assertInstanceOf(
            ClassWithSelfTypeHint::class,
            $this->getMockBuilder(ClassWithSelfTypeHint::class)->getMock()
        );
    }

    public function testStringableClassDoesNotThrow(): void
    {
        /** @var PHPUnit\Framework\MockObject\MockObject|StringableClass $mock */
        $mock = $this->getMockBuilder(StringableClass::class)->getMock();

        $this->assertIsString((string) $mock);
    }

    public function testStringableClassCanBeMocked(): void
    {
        /** @var PHPUnit\Framework\MockObject\MockObject|StringableClass $mock */
        $mock = $this->getMockBuilder(StringableClass::class)->getMock();

        $mock->method('__toString')->willReturn('foo');

        $this->assertSame('foo', (string) $mock);
    }

    public function traversableProvider(): array
    {
        return [
            Traversable::class                  => [Traversable::class],
            '\Traversable'                      => [Traversable::class],
            TraversableMockTestInterface::class => [TraversableMockTestInterface::class],
        ];
    }

    public function testParameterCallbackConstraintOnlyEvaluatedOnce(): void
    {
        $mock                  = $this->getMockBuilder(Foo::class)->addMethods(['bar'])->getMock();
        $expectedNumberOfCalls = 1;
        $callCount             = 0;

        $mock->expects($this->exactly($expectedNumberOfCalls))->method('bar')
            ->with($this->callback(static function ($argument) use (&$callCount)
            {
                return $argument === 'call_' . $callCount++;
            }));

        for ($i = 0; $i < $expectedNumberOfCalls; $i++) {
            $mock->bar('call_' . $i);
        }
    }

    public function testReturnTypesAreMockedCorrectly(): void
    {
        /** @var ClassWithAllPossibleReturnTypes|MockObject $stub */
        $stub = $this->createMock(ClassWithAllPossibleReturnTypes::class);

        $this->assertNull($stub->methodWithNoReturnTypeDeclaration());
        $this->assertSame('', $stub->methodWithStringReturnTypeDeclaration());
        $this->assertSame(0.0, $stub->methodWithFloatReturnTypeDeclaration());
        $this->assertSame(0, $stub->methodWithIntReturnTypeDeclaration());
        $this->assertFalse($stub->methodWithBoolReturnTypeDeclaration());
        $this->assertSame([], $stub->methodWithArrayReturnTypeDeclaration());
        $this->assertInstanceOf(MockObject::class, $stub->methodWithClassReturnTypeDeclaration());
    }

    public function testDisableAutomaticReturnValueGeneration(): void
    {
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->disableAutoReturnValueGeneration()
                     ->getMock();

        $this->expectException(ReturnValueNotConfiguredException::class);
        $this->expectExceptionMessage('Return value inference disabled and no expectation set up for PHPUnit\TestFixture\SomeClass::doSomethingElse()');

        $mock->doSomethingElse(1);
    }

    public function testDisableAutomaticReturnValueGenerationWithToString(): void
    {
        /** @var PHPUnit\Framework\MockObject\MockObject|StringableClass $mock */
        $mock = $this->getMockBuilder(StringableClass::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        (string) $mock;

        try {
            $mock->__phpunit_verify();
            $this->fail('Exception expected');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Return value inference disabled and no expectation set up for PHPUnit\TestFixture\StringableClass::__toString()',
                $e->getMessage()
            );
        }

        $this->resetMockObjects();
    }

    public function testVoidReturnTypeIsMockedCorrectly(): void
    {
        /** @var ClassWithAllPossibleReturnTypes|MockObject $stub */
        $stub = $this->createMock(ClassWithAllPossibleReturnTypes::class);

        $this->assertNull($stub->methodWithVoidReturnTypeDeclaration());
    }

    public function testObjectReturnTypeIsMockedCorrectly(): void
    {
        /** @var ClassWithAllPossibleReturnTypes|MockObject $stub */
        $stub = $this->createMock(ClassWithAllPossibleReturnTypes::class);

        $this->assertInstanceOf(stdClass::class, $stub->methodWithObjectReturnTypeDeclaration());
    }

    /**
     * @requires PHP 8
     */
    public function testUnionReturnTypeIsDoubledCorrectly(): void
    {
        /** @var ClassWithUnionReturnTypes|MockObject $stub */
        $stub = $this->createMock(ClassWithUnionReturnTypes::class);

        $this->assertFalse($stub->returnsBoolOrInt());
    }

    /**
     * @requires PHP 8
     */
    public function testNullableUnionReturnTypeIsDoubledCorrectly(): void
    {
        /** @var ClassWithUnionReturnTypes|MockObject $stub */
        $stub = $this->createMock(ClassWithUnionReturnTypes::class);

        $this->assertNull($stub->returnsBoolOrIntOrNull());
    }

    /**
     * @requires PHP 8
     */
    public function testMixedReturnTypeIsDoubledCorrectly(): void
    {
        /** @var ClassWithUnionReturnTypes|MockObject $stub */
        $stub = $this->createMock(ClassWithUnionReturnTypes::class);

        $this->assertNull($stub->returnsMixed());
    }

    /**
     * @requires PHP 8
     */
    public function testStaticReturnTypeIsDoubledCorrectly(): void
    {
        /** @var ClassWithStaticReturnTypes|Stub $stub */
        $stub = $this->createStub(ClassWithStaticReturnTypes::class);

        $this->assertInstanceOf(ClassWithStaticReturnTypes::class, $stub->returnsStatic());
    }

    /**
     * @requires PHP 8
     */
    public function testUnionReturnTypeWithStaticIsDoubledCorrectly(): void
    {
        /** @var ClassWithStaticReturnTypes|Stub $stub */
        $stub = $this->createStub(ClassWithStaticReturnTypes::class);

        $this->assertInstanceOf(ClassWithStaticReturnTypes::class, $stub->returnsUnionWithStatic());
    }

    /**
     * @requires PHP 8
     */
    public function testNullableStaticReturnTypeIsDoubledCorrectly(): void
    {
        /** @var ClassWithStaticReturnTypes|Stub $stub */
        $stub = $this->createStub(ClassWithStaticReturnTypes::class);

        $this->assertNull($stub->returnsStaticOrNull());
    }

    public function testTraitCanBeDoubled(): void
    {
        $object = $this->getObjectForTrait(ExampleTrait::class);

        $this->assertSame('ohHai', $object->ohHai());
    }

    public function testTraitWithConstructorCanBeDoubled(): void
    {
        $object = $this->getObjectForTrait(TraitWithConstructor::class, ['value']);

        $this->assertSame('value', $object->value());
    }

    /**
     * @requires PHP 8
     */
    public function testMethodThatReturnsIntOrFalseCanBeStubbed(): void
    {
        $i = $this->createStub(InterfaceWithMethodsThatDeclareBooleanReturnTypes::class);

        $i->method('returnsIntOrFalse')->willReturn(false);

        $this->assertFalse($i->returnsIntOrFalse());

        $i = $this->createStub(InterfaceWithMethodsThatDeclareBooleanReturnTypes::class);

        $i->method('returnsIntOrFalse')->willReturn(1);

        $this->assertSame(1, $i->returnsIntOrFalse());
    }

    /**
     * @requires PHP 8
     */
    public function testReturnValueCanBeAutomaticallyGeneratedForMethodThatReturnsIntOrFalse(): void
    {
        $i = $this->createStub(InterfaceWithMethodsThatDeclareBooleanReturnTypes::class);

        $this->assertFalse($i->returnsIntOrFalse());
    }

    /**
     * @requires PHP 8
     */
    public function testMethodThatReturnsBoolCanBeStubbed(): void
    {
        $i = $this->createStub(InterfaceWithMethodsThatDeclareBooleanReturnTypes::class);

        $i->method('returnsBool')->willReturn(false);

        $this->assertFalse($i->returnsBool());
    }

    private function resetMockObjects(): void
    {
        $refl = new ReflectionObject($this);
        $refl = $refl->getParentClass();
        $prop = $refl->getProperty('mockObjects');
        $prop->setAccessible(true);
        $prop->setValue($this, []);
    }
}
