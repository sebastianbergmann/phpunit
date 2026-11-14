<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function iterator_to_array;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\InvalidPropertyException;
use PHPUnit\Framework\TestCase\GlobalStateCapture;
use PHPUnit\TestFixture\PropertyBased\Properties\InvalidPropertiesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyInClassThatRunsTestsInSeparateProcessesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyWithIneligibleAttributesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyWithUnmetRequirementsTest;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;
use PHPUnit\TestFixture\TestBuilder\TestWithBackupExcludeListAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithClassLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithDataProvider;
use PHPUnit\TestFixture\TestBuilder\TestWithInheritedClassLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithMethodLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithoutIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithPreserveGlobalStateAttribute;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use ReflectionClass;
use ReflectionProperty;

#[CoversClass(TestBuilder::class)]
#[Small]
final class TestBuilderTest extends TestCase
{
    public function testBuildsTestWithoutMetadataForIsolation(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithoutIsolationAttributes::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithoutIsolationAttributes::class, $test);

        $test = $test->valueObjectForEvents();

        $this->assertSame(TestWithoutIsolationAttributes::class, $test->className());
        $this->assertSame('testOne', $test->methodName());
        $this->assertTrue($test->metadata()->isBackupGlobals()->isEmpty());
        $this->assertTrue($test->metadata()->isBackupStaticProperties()->isEmpty());
        $this->assertTrue($test->metadata()->isRunTestsInSeparateProcesses()->isEmpty());
    }

    public function testBuildsTestWithClassLevelMetadataForIsolation(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithClassLevelIsolationAttributes::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithClassLevelIsolationAttributes::class, $test);

        $test = $test->valueObjectForEvents();

        $this->assertSame(TestWithClassLevelIsolationAttributes::class, $test->className());
        $this->assertSame('testOne', $test->methodName());
        $this->assertTrue($test->metadata()->isBackupGlobals()->asArray()[0]->enabled());
        $this->assertTrue($test->metadata()->isBackupStaticProperties()->asArray()[0]->enabled());
        $this->assertTrue($test->metadata()->isRunTestsInSeparateProcesses()->isNotEmpty());
    }

    public function testBuildsTestWithMethodLevelMetadataForIsolation(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithMethodLevelIsolationAttributes::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithMethodLevelIsolationAttributes::class, $test);

        $test = $test->valueObjectForEvents();

        $this->assertSame(TestWithMethodLevelIsolationAttributes::class, $test->className());
        $this->assertSame('testOne', $test->methodName());
        $this->assertTrue($test->metadata()->isBackupGlobals()->asArray()[0]->enabled());
        $this->assertTrue($test->metadata()->isBackupStaticProperties()->asArray()[0]->enabled());
        $this->assertTrue($test->metadata()->isRunInSeparateProcess()->isNotEmpty());
    }

    public function testBuildsTestWithInheritedClassLevelMetadataForIsolation(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithInheritedClassLevelIsolationAttributes::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithInheritedClassLevelIsolationAttributes::class, $test);
        $this->assertTrue(new ReflectionProperty(TestCase::class, 'runTestInSeparateProcess')->getValue($test));
    }

    public function testBuildsTestWithMetadataForExcludingGlobalStateFromBackup(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithBackupExcludeListAttributes::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithBackupExcludeListAttributes::class, $test);

        $globalStateCapture = new ReflectionProperty(TestCase::class, 'globalStateCapture')->getValue($test);

        $this->assertSame(
            ['variable'],
            new ReflectionProperty(GlobalStateCapture::class, 'backupGlobalsExcludeList')->getValue($globalStateCapture),
        );

        $this->assertSame(
            [TestWithBackupExcludeListAttributes::class => ['firstProperty', 'secondProperty']],
            new ReflectionProperty(GlobalStateCapture::class, 'backupStaticPropertiesExcludeList')->getValue($globalStateCapture),
        );
    }

    public function testBuildsTestWithMetadataForPreservingGlobalState(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithPreserveGlobalStateAttribute::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithPreserveGlobalStateAttribute::class, $test);
        $this->assertTrue(new ReflectionProperty(TestCase::class, 'preserveGlobalState')->getValue($test));
    }

    public function testBuildsTestWithDataProvider(): void
    {
        $test = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(TestWithDataProvider::class),
            'testOne',
        );

        $this->assertInstanceOf(DataProviderTestSuite::class, $test);

        $test = iterator_to_array($test)[0];

        $this->assertInstanceOf(TestWithDataProvider::class, $test);

        $test = $test->valueObjectForEvents();

        $this->assertSame(TestWithDataProvider::class, $test->className());
        $this->assertSame('testOne', $test->methodName());
        $this->assertTrue($test->testData()->hasDataFromDataProvider());
    }

    public function testBuildsPropertyTestSuiteForProperty(): void
    {
        $suite = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(ValidPropertiesTest::class),
            'testWithParameters',
        );

        $this->assertInstanceOf(PropertyTestSuite::class, $suite);
        $this->assertSame(ValidPropertiesTest::class . '::testWithParameters', $suite->name());
        $this->assertSame(50 * ConfigurationRegistry::get()->propertyExamplesMultiplier(), $suite->numberOfExamples());
        $this->assertCount(1, $suite);
        $this->assertInstanceOf(ValidPropertiesTest::class, $suite->tests()[0]);
    }

    public function testPropertyIsCheckedForConfiguredNumberOfExamplesByDefault(): void
    {
        $suite = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(ValidPropertiesTest::class),
            'testWithoutParameters',
        );

        $this->assertInstanceOf(PropertyTestSuite::class, $suite);
        $this->assertSame(ConfigurationRegistry::get()->propertyExamples() * ConfigurationRegistry::get()->propertyExamplesMultiplier(), $suite->numberOfExamples());
    }

    public function testPropertyIsNeitherRepeatedNorRetried(): void
    {
        $suite = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(ValidPropertiesTest::class),
            'testWithoutParameters',
            [],
            3,
            1,
            3,
        );

        $this->assertInstanceOf(PropertyTestSuite::class, $suite);
        $this->assertCount(1, $suite);
    }

    public function testExamplesOfPropertyAreNotRunInSeparateProcess(): void
    {
        $suite = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(PropertyInClassThatRunsTestsInSeparateProcessesTest::class),
            'testOne',
        );

        $this->assertInstanceOf(PropertyTestSuite::class, $suite);
        $this->assertNull(new ReflectionProperty(TestCase::class, 'runTestInSeparateProcess')->getValue($suite->tests()[0]));
    }

    public function testWarnsWhenPropertyIsAnnotatedWithRepeatRetryOrRunInSeparateProcess(): void
    {
        $messages = [];
        $emitter  = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->exactly(3))
            ->method('testRunnerTriggeredPhpunitWarning')
            ->willReturnCallback(static function (string $message) use (&$messages): void
            {
                $messages[] = $message;
            });

        new TestBuilder($emitter)->build(
            new ReflectionClass(PropertyWithIneligibleAttributesTest::class),
            'testOne',
        );

        $this->assertSame(
            [
                'Method ' . PropertyWithIneligibleAttributesTest::class . '::testOne is annotated with #[Repeat] but is a property and will not be repeated',
                'Method ' . PropertyWithIneligibleAttributesTest::class . '::testOne is annotated with #[Retry] but is a property and will not be retried',
                'Method ' . PropertyWithIneligibleAttributesTest::class . '::testOne is annotated with #[RunInSeparateProcess] but is a property and will not be run in a separate process',
            ],
            $messages,
        );
    }

    public function testDoesNotWarnWhenRepeatOrRetryOnPropertyDoNotAskForMoreThanOneRun(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->never())->method('testRunnerTriggeredPhpunitWarning');

        new TestBuilder($emitter)->build(
            new ReflectionClass(PropertyWithIneligibleAttributesTest::class),
            'testTwo',
        );
    }

    public function testWarnsWhenPropertyIsDeclaredInClassThatRunsTestsInSeparateProcesses(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with(
                'Class ' . PropertyInClassThatRunsTestsInSeparateProcessesTest::class . ' is annotated with #[RunTestsInSeparateProcesses] but ' . PropertyInClassThatRunsTestsInSeparateProcessesTest::class . '::testOne is a property and will not be run in a separate process',
            );

        new TestBuilder($emitter)->build(
            new ReflectionClass(PropertyInClassThatRunsTestsInSeparateProcessesTest::class),
            'testOne',
        );
    }

    public function testReportsPropertyThatCannotBeChecked(): void
    {
        $this->expectException(InvalidPropertyException::class);

        new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(InvalidPropertiesTest::class),
            'testWithUnsupportedType',
        );
    }

    public function testDoesNotValidatePropertyWhoseRequirementsAreNotMet(): void
    {
        $suite = new TestBuilder($this->createStub(Emitter::class))->build(
            new ReflectionClass(PropertyWithUnmetRequirementsTest::class),
            'testOne',
        );

        $this->assertInstanceOf(PropertyTestSuite::class, $suite);
    }
}
