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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\TestBuilder\TestWithClassLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithDataProvider;
use PHPUnit\TestFixture\TestBuilder\TestWithMethodLevelIsolationAttributes;
use PHPUnit\TestFixture\TestBuilder\TestWithoutIsolationAttributes;
use ReflectionClass;

#[CoversClass(TestBuilder::class)]
#[Small]
final class TestBuilderTest extends TestCase
{
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
        $this->assertTrue($test->metadata()->isRunClassInSeparateProcess()->isEmpty());
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
        $this->assertTrue($test->metadata()->isRunClassInSeparateProcess()->isNotEmpty());
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
}
