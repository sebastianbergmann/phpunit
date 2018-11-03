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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Runner\BaseTestRunner;

class TestCaseTest extends TestCase
{
    protected static $testStatic      = 0;

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
        self::$testStatic     = 123;
    }

    /**
     * @depends testStaticAttributesBackupPre
     */
    public function testStaticAttributesBackupPost(): void
    {
        $this->assertNotSame($GLOBALS['singleton'], \Singleton::getInstance());
        $this->assertSame(0, self::$testStatic);
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
            'Function testFuncTwo is required.' . \PHP_EOL .
            'Setting "not_a_setting" must be "Off".' . \PHP_EOL .
            'Extension testExtOne is required.' . \PHP_EOL .
            'Extension testExtTwo is required.' . \PHP_EOL .
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
     * @expectedException \TypeError
     */
    public function testTypeErrorCanBeExpected(): void
    {
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
