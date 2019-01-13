<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Runner\BaseTestRunner;

class TestCaseTest extends TestCase
{
    protected static $testStatic      = 456;

    protected $backupGlobalsBlacklist = ['i', 'singleton'];

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['a']  = 'a';
        $_ENV['b']     = 'b';
        $_POST['c']    = 'c';
        $_GET['d']     = 'd';
        $_COOKIE['e']  = 'e';
        $_SERVER['f']  = 'f';
        $_FILES['g']   = 'g';
        $_REQUEST['h'] = 'h';
        $GLOBALS['i']  = 'i';
    }

    public static function tearDownAfterClass(): void
    {
        unset(
            $GLOBALS['a'],
            $_ENV['b'],
            $_POST['c'],
            $_GET['d'],
            $_COOKIE['e'],
            $_SERVER['f'],
            $_FILES['g'],
            $_REQUEST['h'],
            $GLOBALS['i']
        );
    }

    public function testCaseToString(): void
    {
        $this->assertEquals(
            'PHPUnit\Framework\TestCaseTest::testCaseToString',
            $this->toString()
        );
    }

    public function testSuccess(): void
    {
        $test   = new \Success;
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testFailure(): void
    {
        $test   = new \Failure;
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testError(): void
    {
        $test   = new \TestError;
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_ERROR, $test->getStatus());
        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testSkipped(): void
    {
        $test   = new \TestSkipped;
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_SKIPPED, $test->getStatus());
        $this->assertEquals('Skipped test', $test->getStatusMessage());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(1, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testIncomplete(): void
    {
        $test   = new \TestIncomplete;
        $result = $test->run();

        $this->assertEquals(BaseTestRunner::STATUS_INCOMPLETE, $test->getStatus());
        $this->assertEquals('Incomplete test', $test->getStatusMessage());
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->skippedCount());
        $this->assertCount(1, $result);
    }

    public function testExceptionInSetUp(): void
    {
        $test   = new \ExceptionInSetUpTest('testSomething');
        $test->run();

        $this->assertTrue($test->setUp);
        $this->assertFalse($test->assertPreConditions);
        $this->assertFalse($test->testSomething);
        $this->assertFalse($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInAssertPreConditions(): void
    {
        $test   = new \ExceptionInAssertPreConditionsTest('testSomething');
        $test->run();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertFalse($test->testSomething);
        $this->assertFalse($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInTest(): void
    {
        $test   = new \ExceptionInTest('testSomething');
        $test->run();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertTrue($test->testSomething);
        $this->assertFalse($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInAssertPostConditions(): void
    {
        $test   = new \ExceptionInAssertPostConditionsTest('testSomething');
        $test->run();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertTrue($test->testSomething);
        $this->assertTrue($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInTearDown(): void
    {
        $test   = new \ExceptionInTearDownTest('testSomething');
        $test->run();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertTrue($test->testSomething);
        $this->assertTrue($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
        $this->assertEquals(BaseTestRunner::STATUS_ERROR, $test->getStatus());
    }

    public function testExceptionInTestIsDetectedInTeardown(): void
    {
        $test   = new \ExceptionInTestDetectedInTeardown('testSomething');
        $test->run();

        $this->assertTrue($test->exceptionDetected);
    }

    public function testNoArgTestCasePasses(): void
    {
        $result = new TestResult;
        $t      = new TestSuite(\NoArgTestCaseTest::class);

        $t->run($result);

        $this->assertCount(1, $result);
        $this->assertEquals(0, $result->failureCount());
        $this->assertEquals(0, $result->errorCount());
    }

    public function testWasRun(): void
    {
        $test = new \WasRun;
        $test->run();

        $this->assertTrue($test->wasRun);
    }

    public function testException(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExceptionWithEmptyMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExceptionWithNullMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExceptionWithMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);
        $test->expectExceptionMessage('A runtime error occurred');

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExceptionWithWrongMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);
        $test->expectExceptionMessage('A logic error occurred');

        $result = $test->run();

        $this->assertEquals(1, $result->failureCount());
        $this->assertCount(1, $result);
        $this->assertEquals(
            "Failed asserting that exception message 'A runtime error occurred' contains 'A logic error occurred'.",
            $test->getStatusMessage()
        );
    }

    public function testExceptionWithRegexpMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);
        $test->expectExceptionMessageRegExp('/runtime .*? occurred/');

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExceptionWithWrongRegexpMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);
        $test->expectExceptionMessageRegExp('/logic .*? occurred/');

        $result = $test->run();

        $this->assertEquals(1, $result->failureCount());
        $this->assertCount(1, $result);
        $this->assertEquals(
            "Failed asserting that exception message 'A runtime error occurred' matches '/logic .*? occurred/'.",
            $test->getStatusMessage()
        );
    }

    public function testExceptionWithInvalidRegexpMessage(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);
        $test->expectExceptionMessageRegExp('#runtime .*? occurred/');

        $test->run();

        $this->assertEquals(
            "Invalid expected exception message regex given: '#runtime .*? occurred/'",
            $test->getStatusMessage()
        );
    }

    public function testNoException(): void
    {
        $test = new \ThrowNoExceptionTestCase('test');
        $test->expectException(\RuntimeException::class);

        $result = $test->run();

        $this->assertEquals(1, $result->failureCount());
        $this->assertCount(1, $result);
    }

    public function testWrongException(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectException(\InvalidArgumentException::class);

        $result = $test->run();

        $this->assertEquals(1, $result->failureCount());
        $this->assertCount(1, $result);
    }

    public function testDoesNotPerformAssertions(): void
    {
        $test = new \DoNoAssertionTestCase('testNothing');
        $test->expectNotToPerformAssertions();

        $result = $test->run();

        $this->assertEquals(0, $result->riskyCount());
        $this->assertCount(1, $result);
    }

    /**
     * @backupGlobals enabled
     */
    public function testGlobalsBackupPre(): void
    {
        global $a;
        global $i;

        $this->assertEquals('a', $a);
        $this->assertEquals('a', $GLOBALS['a']);
        $this->assertEquals('b', $_ENV['b']);
        $this->assertEquals('c', $_POST['c']);
        $this->assertEquals('d', $_GET['d']);
        $this->assertEquals('e', $_COOKIE['e']);
        $this->assertEquals('f', $_SERVER['f']);
        $this->assertEquals('g', $_FILES['g']);
        $this->assertEquals('h', $_REQUEST['h']);
        $this->assertEquals('i', $i);
        $this->assertEquals('i', $GLOBALS['i']);

        $GLOBALS['a']   = 'aa';
        $GLOBALS['foo'] = 'bar';
        $_ENV['b']      = 'bb';
        $_POST['c']     = 'cc';
        $_GET['d']      = 'dd';
        $_COOKIE['e']   = 'ee';
        $_SERVER['f']   = 'ff';
        $_FILES['g']    = 'gg';
        $_REQUEST['h']  = 'hh';
        $GLOBALS['i']   = 'ii';

        $this->assertEquals('aa', $a);
        $this->assertEquals('aa', $GLOBALS['a']);
        $this->assertEquals('bar', $GLOBALS['foo']);
        $this->assertEquals('bb', $_ENV['b']);
        $this->assertEquals('cc', $_POST['c']);
        $this->assertEquals('dd', $_GET['d']);
        $this->assertEquals('ee', $_COOKIE['e']);
        $this->assertEquals('ff', $_SERVER['f']);
        $this->assertEquals('gg', $_FILES['g']);
        $this->assertEquals('hh', $_REQUEST['h']);
        $this->assertEquals('ii', $i);
        $this->assertEquals('ii', $GLOBALS['i']);
    }

    /**
     * @depends testGlobalsBackupPre
     */
    public function testGlobalsBackupPost(): void
    {
        global $a;
        global $i;

        $this->assertEquals('a', $a);
        $this->assertEquals('a', $GLOBALS['a']);
        $this->assertEquals('b', $_ENV['b']);
        $this->assertEquals('c', $_POST['c']);
        $this->assertEquals('d', $_GET['d']);
        $this->assertEquals('e', $_COOKIE['e']);
        $this->assertEquals('f', $_SERVER['f']);
        $this->assertEquals('g', $_FILES['g']);
        $this->assertEquals('h', $_REQUEST['h']);
        $this->assertEquals('ii', $i);
        $this->assertEquals('ii', $GLOBALS['i']);

        $this->assertArrayNotHasKey('foo', $GLOBALS);
    }

    /**
     * @backupGlobals enabled
     * @backupStaticAttributes enabled
     *
     * @doesNotPerformAssertions
     */
    public function testStaticAttributesBackupPre(): void
    {
        $GLOBALS['singleton'] = \Singleton::getInstance();
        $GLOBALS['i']         = 'not reset by backup';

        $GLOBALS['j']         = 'reset by backup';
        self::$testStatic     = 123;
    }

    /**
     * @depends testStaticAttributesBackupPre
     */
    public function testStaticAttributesBackupPost(): void
    {
        // Snapshots made by @backupGlobals
        $this->assertSame(\Singleton::getInstance(), $GLOBALS['singleton']);
        $this->assertSame('not reset by backup', $GLOBALS['i']);

        // Reset global
        $this->assertArrayNotHasKey('j', $GLOBALS);

        // Static reset to original state by @backupStaticAttributes
        $this->assertSame(456, self::$testStatic);
    }

    public function testIsInIsolationReturnsFalse(): void
    {
        $test   = new \IsolationTest('testIsInIsolationReturnsFalse');
        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testIsInIsolationReturnsTrue(): void
    {
        $test   = new \IsolationTest('testIsInIsolationReturnsTrue');
        $test->setRunTestInSeparateProcess(true);
        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExpectOutputStringFooActualFoo(): void
    {
        $test   = new \OutputTestCase('testExpectOutputStringFooActualFoo');
        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExpectOutputStringFooActualBar(): void
    {
        $test   = new \OutputTestCase('testExpectOutputStringFooActualBar');
        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertFalse($result->wasSuccessful());
    }

    public function testExpectOutputRegexFooActualFoo(): void
    {
        $test   = new \OutputTestCase('testExpectOutputRegexFooActualFoo');
        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
    }

    public function testExpectOutputRegexFooActualBar(): void
    {
        $test   = new \OutputTestCase('testExpectOutputRegexFooActualBar');
        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertFalse($result->wasSuccessful());
    }

    public function testSkipsIfRequiresHigherVersionOfPHPUnit(): void
    {
        $test   = new \RequirementsTest('testAlwaysSkip');
        $result = $test->run();

        $this->assertEquals(1, $result->skippedCount());
        $this->assertEquals(
            'PHPUnit >= 1111111 is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsIfRequiresHigherVersionOfPHP(): void
    {
        $test   = new \RequirementsTest('testAlwaysSkip2');
        $result = $test->run();

        $this->assertEquals(1, $result->skippedCount());
        $this->assertEquals(
            'PHP >= 9999999 is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsIfRequiresNonExistingOs(): void
    {
        $test   = new \RequirementsTest('testAlwaysSkip3');
        $result = $test->run();

        $this->assertEquals(1, $result->skippedCount());
        $this->assertEquals(
            'Operating system matching /DOESNOTEXIST/i is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsIfRequiresNonExistingOsFamily(): void
    {
        $test   = new \RequirementsTest('testAlwaysSkip4');
        $result = $test->run();

        $this->assertEquals(1, $result->skippedCount());
        $this->assertEquals(
            'Operating system DOESNOTEXIST is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsIfRequiresNonExistingFunction(): void
    {
        $test   = new \RequirementsTest('testNine');
        $result = $test->run();

        $this->assertEquals(1, $result->skippedCount());
        $this->assertEquals(
            'Function testFunc is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsIfRequiresNonExistingExtension(): void
    {
        $test   = new \RequirementsTest('testTen');
        $test->run();

        $this->assertEquals(
            'Extension testExt is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsIfRequiresExtensionWithAMinimumVersion(): void
    {
        $test   = new \RequirementsTest('testSpecificExtensionVersion');
        $test->run();

        $this->assertEquals(
            'Extension testExt >= 1.8.0 is required.',
            $test->getStatusMessage()
        );
    }

    public function testSkipsProvidesMessagesForAllSkippingReasons(): void
    {
        $test   = new \RequirementsTest('testAllPossibleRequirements');
        $test->run();

        $this->assertEquals(
            'PHP >= 99-dev is required.' . \PHP_EOL .
            'PHPUnit >= 9-dev is required.' . \PHP_EOL .
            'Operating system matching /DOESNOTEXIST/i is required.' . \PHP_EOL .
            'Function testFuncOne is required.' . \PHP_EOL .
            'Function testFunc2 is required.' . \PHP_EOL .
            'Setting "not_a_setting" must be "Off".' . \PHP_EOL .
            'Extension testExtOne is required.' . \PHP_EOL .
            'Extension testExt2 is required.' . \PHP_EOL .
            'Extension testExtThree >= 2.0 is required.',
            $test->getStatusMessage()
        );
    }

    public function testRequiringAnExistingMethodDoesNotSkip(): void
    {
        $test   = new \RequirementsTest('testExistingMethod');
        $result = $test->run();
        $this->assertEquals(0, $result->skippedCount());
    }

    public function testRequiringAnExistingFunctionDoesNotSkip(): void
    {
        $test   = new \RequirementsTest('testExistingFunction');
        $result = $test->run();
        $this->assertEquals(0, $result->skippedCount());
    }

    public function testRequiringAnExistingExtensionDoesNotSkip(): void
    {
        $test   = new \RequirementsTest('testExistingExtension');
        $result = $test->run();
        $this->assertEquals(0, $result->skippedCount());
    }

    public function testRequiringAnExistingOsDoesNotSkip(): void
    {
        $test   = new \RequirementsTest('testExistingOs');
        $result = $test->run();
        $this->assertEquals(0, $result->skippedCount());
    }

    public function testRequiringASetting(): void
    {
        $test   = new \RequirementsTest('testSettingDisplayErrorsOn');

        // Get this so we can return it to whatever it was before the test.
        $displayErrorsVal = \ini_get('display_errors');

        \ini_set('display_errors', 'On');
        $result = $test->run();
        $this->assertEquals(0, $result->skippedCount());

        \ini_set('display_errors', 'Off');
        $result = $test->run();
        $this->assertEquals(1, $result->skippedCount());

        \ini_set('display_errors', $displayErrorsVal);
    }

    public function testCurrentWorkingDirectoryIsRestored(): void
    {
        $expectedCwd = \getcwd();

        $test = new \ChangeCurrentWorkingDirectoryTest('testSomethingThatChangesTheCwd');
        $test->run();

        $this->assertSame($expectedCwd, \getcwd());
    }

    /**
     * @requires PHP 7
     */
    public function testTypeErrorCanBeExpected(): void
    {
        $this->expectException(\TypeError::class);

        $o = new \ClassWithScalarTypeDeclarations;
        $o->foo(null, null);
    }

    public function testCreateMockFromClassName(): void
    {
        $mock = $this->createMock(\Mockable::class);

        $this->assertInstanceOf(\Mockable::class, $mock);
        $this->assertInstanceOf(MockObject::class, $mock);
    }

    public function testCreateMockMocksAllMethods(): void
    {
        /** @var \Mockable $mock */
        $mock = $this->createMock(\Mockable::class);

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testCreatePartialMockDoesNotMockAllMethods(): void
    {
        /** @var \Mockable $mock */
        $mock = $this->createPartialMock(\Mockable::class, ['mockableMethod']);

        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testCreatePartialMockCanMockNoMethods(): void
    {
        /** @var \Mockable $mock */
        $mock = $this->createPartialMock(\Mockable::class, []);

        $this->assertTrue($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testCreateMockSkipsConstructor(): void
    {
        /** @var \Mockable $mock */
        $mock = $this->createMock(\Mockable::class);

        $this->assertNull($mock->constructorArgs);
    }

    public function testCreateMockDisablesOriginalClone(): void
    {
        /** @var \Mockable $mock */
        $mock = $this->createMock(\Mockable::class);

        $cloned = clone $mock;
        $this->assertNull($cloned->cloned);
    }

    public function testConfiguredMockCanBeCreated(): void
    {
        /** @var \Mockable $mock */
        $mock = $this->createConfiguredMock(
            \Mockable::class,
            [
                'mockableMethod' => false,
            ]
        );

        $this->assertFalse($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testProvidingOfAutoreferencedArray(): void
    {
        $test = new \TestAutoreferenced('testJsonEncodeException', $this->getAutoreferencedArray());
        $test->runBare();

        $this->assertIsArray($test->myTestData);
        $this->assertArrayHasKey('data', $test->myTestData);
        $this->assertEquals($test->myTestData['data'][0], $test->myTestData['data']);
    }

    public function testProvidingArrayThatMixesObjectsAndScalars(): void
    {
        $data = [
            [123],
            ['foo'],
            [$this->createMock(\Mockable::class)],
        ];

        $test = new \TestAutoreferenced('testJsonEncodeException', [$data]);
        $test->runBare();

        $this->assertIsArray($test->myTestData);
        $this->assertSame($data, $test->myTestData);
    }

    public function testGettingNullTestResultObject(): void
    {
        $test = new \Success;
        $this->assertNull($test->getTestResultObject());
    }

    /**
     * Test processExceptionConstraints is private
     *
     * Validate that processExceptionConstraints method is private, in order to avoid self definition issues.
     *
     * @throws \ReflectionException
     * @group  ExceptionConstraints
     */
    public function testProcessExceptionConstraintIsPrivate(): void
    {
        $this->assertTrue(
            \method_exists(TestCase::class, 'processExceptionConstraints'),
            \sprintf(
                'processExceptionConstraints method have to exist into the "%s" class in order to support self' .
                ' defined expected constraint feature',
                \TestClass::class
            )
        );

        $reflection = new \ReflectionMethod(TestCase::class, 'processExceptionConstraints');
        $this->assertTrue(
            $reflection->isPrivate(),
            'processExceptionConstraints method should be private to avoid self definition issues'
        );
    }

    /**
     * Test processExceptionConstraints expect throwable
     *
     * Validate that processExceptionConstraints method expect a \Throwable instance, in order to offer this parameter
     * to the processed constraints.
     *
     * @depends testProcessExceptionConstraintIsPrivate
     *
     * @throws \ReflectionException
     * @group   ExceptionConstraints
     */
    public function testProcessExceptionConstraintExpectThrowable(): void
    {
        $reflection = new \ReflectionMethod(TestCase::class, 'processExceptionConstraints');
        $parameters = $reflection->getParameters();

        foreach ($parameters as $parameter) {
            if ($parameter->getName() == 'exception') {
                $this->assertEquals(
                    \Throwable::class,
                    $parameter->getType()->getName(),
                    'processExceptionConstraints method must receive an exception parameter as a \Throwable instance'
                );

                return;
            }
        }
    }

    /**
     * test ProcessExceptionConstraints execute constraints
     *
     * Validate that ProcessExceptionConstraints method execute the whole set of constraints without dependencies
     *
     * @throws \ReflectionException
     * @group  ExceptionConstraints
     */
    public function testProcessExceptionConstraintsExecuteConstraints(): void
    {
        $exception  = $this->createMock(\Throwable::class);
        $callback   = $this->createPartialMock(Callback::class, ['matches']);
        $instanceOf = $this->createPartialMock(IsInstanceOf::class, ['matches']);

        $callback->expects($this->once())
            ->method('matches')
            ->with($this->identicalTo($exception))
            ->willReturn(true);

        $instanceOf->expects($this->once())
            ->method('matches')
            ->with($this->identicalTo($exception))
            ->willReturn(true);

        $reflection = new \ReflectionClass(\ThrowExceptionTestCase::class);
        $instance   = $reflection->newInstanceWithoutConstructor();

        $storeProperty = new \ReflectionProperty(TestCase::class, 'exceptionConstraints');
        $storeProperty->setAccessible(true);
        $storeProperty->setValue(
            $instance,
            [
                $callback,
                $instanceOf,
            ]
        );

        $processMethod = new \ReflectionMethod(TestCase::class, 'processExceptionConstraints');
        $processMethod->setAccessible(true);

        $this->assertNull(
            $processMethod->invoke($instance, $exception),
            'processExceptionConstraints method is not expected to return a value'
        );
    }

    /**
     * test exceptionConstraints is reset
     *
     * Validate the TestCase::resetExceptionConstraints method and more specifically its capability to reset the
     * exceptionConstraints property as an empty array.
     *
     * @throws \ReflectionException
     * @group  ExceptionConstraints
     */
    public function testExceptionConstraintsIsReset(): void
    {
        $reflection = new \ReflectionClass(\ThrowExceptionTestCase::class);
        $this->assertTrue(
            $reflection->hasMethod('resetExceptionConstraints'),
            \sprintf(
                'resetExceptionConstraints is expected to be defined in the "%s" class',
                TestCase::class
            )
        );

        $instance = $reflection->newInstanceWithoutConstructor();

        $storeProperty = new \ReflectionProperty(TestCase::class, 'exceptionConstraints');
        $storeProperty->setAccessible(true);
        $storeProperty->setValue($instance, [$this->createMock(Constraint::class)]);

        $processMethod = new \ReflectionMethod(TestCase::class, 'resetExceptionConstraints');
        $processMethod->setAccessible(true);

        $this->assertTrue(
            $processMethod->isPrivate(),
            'resetExceptionConstraints is expected to be private in order to ensure the reset goals'
        );

        $this->assertSame(
            $instance,
            $processMethod->invoke($instance),
            'resetExceptionConstraints is expected to offer fluent interface'
        );

        $value = $storeProperty->getValue($instance);
        $this->assertTrue(
            \is_array($value),
            'resetExceptionConstraints is expected to reset the exceptionConstraints to an array'
        );
        $this->assertEmpty(
            $value,
            'resetExceptionConstraints is expected to reset the exceptionConstraints to an empty array'
        );
    }

    /**
     * test constraint message is overrideable
     *
     * Validate that the setSelfDefinedConstraintMessage method is able to override the default
     * selfDefinedConstraintMessage value
     *
     * @throws \ReflectionException
     * @group  ExceptionConstraints
     */
    public function testConstraintMessageIsOverrideable(): void
    {
        $reflection = new \ReflectionClass(\ThrowExceptionTestCase::class);
        $this->assertTrue(
            $reflection->hasMethod('setSelfDefinedConstraintMessage'),
            \sprintf(
                'setSelfDefinedConstraintMessage is expected to be defined in the "%s" class',
                TestCase::class
            )
        );
        $instance = $reflection->newInstanceWithoutConstructor();

        $processMethod = new \ReflectionMethod(TestCase::class, 'setSelfDefinedConstraintMessage');
        $this->assertTrue(
            $processMethod->isPublic(),
            'setSelfDefinedConstraintMessage is expected to be public in order to override the message property'
        );

        $storeProperty = new \ReflectionProperty(TestCase::class, 'selfDefinedConstraintMessage');
        $storeProperty->setAccessible(true);

        $defaultMessage = 'Failed asserting that exception with user defined constraint is thrown';
        $this->assertEquals(
            $defaultMessage,
            $storeProperty->getValue($instance),
            \sprintf(
                'selfDefinedConstraintMessage is expected to be defined with a specific default message ("%s")',
                $defaultMessage
            )
        );

        $newMessage = 'New message sample';
        $instance->setSelfDefinedConstraintMessage($newMessage);
        $this->assertEquals(
            $newMessage,
            $storeProperty->getValue($instance)
        );
    }

    /**
     * Test exception constraint it gettable
     *
     * Validate the getExceptionConstraints method offer access to the internal exception constraint store
     *
     * @throws \ReflectionException
     * @group  ExceptionConstraints
     */
    public function testExceptionConstraintsIsGettable(): void
    {
        $reflection = new \ReflectionClass(\ThrowExceptionTestCase::class);
        $this->assertTrue(
            $reflection->hasMethod('getExceptionConstraints'),
            \sprintf(
                'getExceptionConstraints is expected to be defined in the "%s" class',
                TestCase::class
            )
        );
        $instance = $reflection->newInstanceWithoutConstructor();

        $processMethod = new \ReflectionMethod(TestCase::class, 'getExceptionConstraints');
        $this->assertTrue(
            $processMethod->isPublic(),
            'getExceptionConstraints is expected to be public in order give access to the exceptionConstraints store'
        );

        $storeProperty = new \ReflectionProperty(TestCase::class, 'exceptionConstraints');
        $storeProperty->setAccessible(true);
        $constraintSet = [$this->createMock(Constraint::class)];
        $storeProperty->setValue($instance, $constraintSet);

        $this->assertSame(
            $constraintSet,
            $instance->getExceptionConstraints(),
            'getExceptionConstraints is expected to return the exact content of the exceptionConstraints store'
        );
    }

    /**
     * test exception constraint is injectable
     *
     * Validate the addExpectedExceptionConstraint method offer injection to the internal exception constraint store
     *
     * @throws \ReflectionException
     * @group  ExceptionConstraints
     */
    public function testExceptionConstraintIsInjectable(): void
    {
        $reflection = new \ReflectionClass(\ThrowExceptionTestCase::class);
        $this->assertTrue(
            $reflection->hasMethod('addExpectedExceptionConstraint'),
            \sprintf(
                'addExpectedExceptionConstraint is expected to be defined in the "%s" class',
                TestCase::class
            )
        );
        $instance = $reflection->newInstanceWithoutConstructor();

        $processMethod = new \ReflectionMethod(TestCase::class, 'addExpectedExceptionConstraint');
        $this->assertTrue(
            $processMethod->isPublic(),
            'addExpectedExceptionConstraint is expected to be public in order to add new exception constraint'
        );

        $parameterValidated = false;

        foreach ($processMethod->getParameters() as $parameter) {
            if ($parameter->getName() == 'constraint') {
                $this->assertEquals(
                    Constraint::class,
                    $parameter->getType()->getName(),
                    'addExpectedExceptionConstraint method must receive a Constraint instance as constraint parameter'
                );
                $parameterValidated = true;
            }
        }

        if (!$parameterValidated) {
            $this->fail('addExpectedExceptionConstraint method must receive a constraint parameter');
        }

        $storeProperty = new \ReflectionProperty(TestCase::class, 'exceptionConstraints');
        $storeProperty->setAccessible(true);
        $initialConstraint = $this->createMock(Constraint::class);
        $storeProperty->setValue($instance, [$initialConstraint]);

        $injectedConstraint = $this->createMock(Constraint::class);
        $instance->addExpectedExceptionConstraint($injectedConstraint);

        $this->assertContains(
            $initialConstraint,
            $storeProperty->getValue($instance),
            'addExpectedExceptionConstraint is not expected to drop the initial content of the internal store'
        );
        $this->assertContains(
            $injectedConstraint,
            $storeProperty->getValue($instance),
            'addExpectedExceptionConstraint is expected to push the new constraint into the internal store'
        );
    }

    /**
     * Test exception constraint is settable
     *
     * Validate the expectedExceptionConstraint method will set the internal store of constraints
     *
     * @depends testExceptionConstraintIsInjectable
     * @depends testExceptionConstraintsIsReset
     * @group   ExceptionConstraints
     *
     * @throws \ReflectionException
     */
    public function testExceptionConstraintIsSettable(): void
    {
        $instance = $this->createPartialMock(
            \ThrowExceptionTestCase::class,
            ['addExpectedExceptionConstraint']
        );

        $uncalled = $this->createPartialMock(Callback::class, ['matches']);
        $uncalled->expects($this->never())
            ->method('matches');

        $storeProperty = new \ReflectionProperty(TestCase::class, 'exceptionConstraints');
        $storeProperty->setAccessible(true);
        $storeProperty->setValue($instance, [$uncalled]);

        $callable   = $this->createMock(Callback::class);
        $instanceOf = $this->createMock(IsInstanceOf::class);

        $instance->expects($this->exactly(2))
            ->method('addExpectedExceptionConstraint')
            ->withConsecutive(
                $this->identicalTo($callable),
                $this->identicalTo($instanceOf)
            );

        $instance->expectedExceptionConstraint([$callable, $instanceOf]);
    }

    /**
     * Test set exception constraint with message
     *
     * Validate the expectedExceptionConstraint method with a message in case of avoided constraint execution
     *
     * @depends testExceptionConstraintIsSettable
     * @depends testConstraintMessageIsOverrideable
     * @group   ExceptionConstraints
     */
    public function testSetExceptionConstraintWithMessage(): void
    {
        $instance = $this->createPartialMock(
            \ThrowExceptionTestCase::class,
            ['setSelfDefinedConstraintMessage']
        );

        $message = 'Message sample';

        $instance->expects($this->once())
            ->method('setSelfDefinedConstraintMessage')
            ->with($this->equalTo($message));

        $instance->expectedExceptionConstraint([], $message);
    }

    /**
     * Test exception constraint
     *
     * Validate the self defined exception constraint feature support during test execution
     *
     * @group  ExceptionConstraints
     *
     * @throws \ReflectionException
     */
    public function testExceptionConstraint(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectedExceptionConstraint(
            [
                $this->isInstanceOf(\RuntimeException::class),
            ]
        );

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertTrue($result->wasSuccessful());
        $this->assertEquals(0, $result->failureCount());
    }

    /**
     * Test exception constraint fail on unvalidated
     *
     * Validate the self defined exception constraint feature comportment when the constraints are not validated during
     * the test execution
     *
     * @group  ExceptionConstraints
     *
     * @throws \ReflectionException
     */
    public function testExceptionConstraintFailOnUnvalidated(): void
    {
        $test = new \ThrowExceptionTestCase('test');
        $test->expectedExceptionConstraint(
            [
                $this->isInstanceOf(\LogicException::class),
            ]
        );

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertFalse($result->wasSuccessful());
        $this->assertEquals(1, $result->failureCount());
    }

    /**
     * Test exception constraint fail on avoided
     *
     * Validate the self defined exception constraint feature comportment when the constraints are not processed during
     * the test execution
     *
     * @group  ExceptionConstraints
     *
     * @throws \ReflectionException
     */
    public function testExceptionConstraintFailOnAvoided(): void
    {
        $test = new \ConcreteTest('testTwo');
        $test->expectedExceptionConstraint(
            [
                new Callback(function () {
                    return true;
                }),
            ]
        );

        $result = $test->run();

        $this->assertCount(1, $result);
        $this->assertFalse($result->wasSuccessful());
        $this->assertEquals('Failed asserting that exception with user defined constraint is thrown', $test->getStatusMessage());
    }

    /**
     * @return array<string, array>
     */
    private function getAutoreferencedArray()
    {
        $recursionData   = [];
        $recursionData[] = &$recursionData;

        return [
            'RECURSION' => [
                'data' => $recursionData,
            ],
        ];
    }
}
