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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase\GlobalStateCapture;
use PHPUnit\TestFixture\TestBuilder\TestWithBackupExcludeListAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithClassLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithDataProvider;
use PHPUnit\TestFixture\TestBuilder\TestWithInheritedClassLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithMethodLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithoutIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithPreserveGlobalStateAttribute;
use ReflectionClass;
use ReflectionProperty;

#[CoversClass(TestBuilder::class)]
#[Small]
final class TestBuilderTest extends TestCase
{
    /**
     * @return array<string, array{class-string<TestCase>, non-empty-string}>
     */
    public static function provider(): array
    {
        return [
            'without metadata for isolation'                    => [TestWithoutIsolationAttributes::class, 'testOne'],
            'with class-level metadata for isolation'           => [TestWithClassLevelIsolationAttributes::class, 'testOne'],
            'with method-level metadata for isolation'          => [TestWithMethodLevelIsolationAttributes::class, 'testOne'],
            'with inherited class-level metadata for isolation' => [TestWithInheritedClassLevelIsolationAttributes::class, 'testOne'],
            'with metadata for excluding global state'          => [TestWithBackupExcludeListAttributes::class, 'testOne'],
            'with data provider'                                => [TestWithDataProvider::class, 'testOne'],
        ];
    }

    public function testBuildsTestWithoutMetadataForIsolation(): void
    {
        $test = (new TestBuilder)->build(
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
        $test = (new TestBuilder)->build(
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
        $test = (new TestBuilder)->build(
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
        $test = (new TestBuilder)->build(
            new ReflectionClass(TestWithInheritedClassLevelIsolationAttributes::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithInheritedClassLevelIsolationAttributes::class, $test);
        $this->assertTrue(new ReflectionProperty(TestCase::class, 'runTestInSeparateProcess')->getValue($test));
    }

    public function testBuildsTestWithMetadataForExcludingGlobalStateFromBackup(): void
    {
        $test = (new TestBuilder)->build(
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
        $test = (new TestBuilder)->build(
            new ReflectionClass(TestWithPreserveGlobalStateAttribute::class),
            'testOne',
        );

        $this->assertInstanceOf(TestWithPreserveGlobalStateAttribute::class, $test);
        $this->assertTrue(new ReflectionProperty(TestCase::class, 'preserveGlobalState')->getValue($test));
    }

    public function testBuildsTestWithDataProvider(): void
    {
        $test = (new TestBuilder)->build(
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

    public function testConfiguresTestWithClassLevelMetadataForIsolation(): void
    {
        $test = new TestWithClassLevelIsolationAttributes('testOne');

        (new TestBuilder)->configure($test, TestWithClassLevelIsolationAttributes::class, 'testOne');

        $this->assertTrue(new ReflectionProperty(TestCase::class, 'runTestInSeparateProcess')->getValue($test));

        $globalStateCapture = new ReflectionProperty(TestCase::class, 'globalStateCapture')->getValue($test);

        $this->assertTrue(new ReflectionProperty(GlobalStateCapture::class, 'backupGlobals')->getValue($globalStateCapture));
        $this->assertTrue(new ReflectionProperty(GlobalStateCapture::class, 'backupStaticProperties')->getValue($globalStateCapture));
    }

    public function testConfiguresTestWithMetadataForExcludingGlobalStateFromBackup(): void
    {
        $test = new TestWithBackupExcludeListAttributes('testOne');

        (new TestBuilder)->configure($test, TestWithBackupExcludeListAttributes::class, 'testOne');

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

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    #[DataProvider('provider')]
    public function testConfiguresTestTheSameWayAsItBuildsIt(string $className, string $methodName): void
    {
        $built = (new TestBuilder)->build(new ReflectionClass($className), $methodName);

        if ($built instanceof DataProviderTestSuite) {
            $built = iterator_to_array($built)[0];
        }

        $this->assertInstanceOf(TestCase::class, $built);

        $configured = new $className($methodName);

        (new TestBuilder)->configure($configured, $className, $methodName);

        $this->assertSame(
            $this->configurationOf($built),
            $this->configurationOf($configured),
        );
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    private function configurationOf(TestCase $test): array
    {
        $globalStateCapture = new ReflectionProperty(TestCase::class, 'globalStateCapture')->getValue($test);

        return [
            'runTestInSeparateProcess'          => new ReflectionProperty(TestCase::class, 'runTestInSeparateProcess')->getValue($test),
            'preserveGlobalState'               => new ReflectionProperty(TestCase::class, 'preserveGlobalState')->getValue($test),
            'backupGlobals'                     => new ReflectionProperty(GlobalStateCapture::class, 'backupGlobals')->getValue($globalStateCapture),
            'backupGlobalsExcludeList'          => new ReflectionProperty(GlobalStateCapture::class, 'backupGlobalsExcludeList')->getValue($globalStateCapture),
            'backupStaticProperties'            => new ReflectionProperty(GlobalStateCapture::class, 'backupStaticProperties')->getValue($globalStateCapture),
            'backupStaticPropertiesExcludeList' => new ReflectionProperty(GlobalStateCapture::class, 'backupStaticPropertiesExcludeList')->getValue($globalStateCapture),
        ];
    }
}
