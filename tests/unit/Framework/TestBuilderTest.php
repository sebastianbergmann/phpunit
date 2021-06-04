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

use PHPUnit\TestFixture\EmptyDataProviderTest;
use PHPUnit\TestFixture\ModifiedConstructorTestCase;
use PHPUnit\TestFixture\TestWithAnnotations;
use ReflectionClass;

/**
 * @covers \PHPUnit\Framework\TestBuilder
 */
final class TestBuilderTest extends TestCase
{
    public function testCreateTestForTestClassWithModifiedConstructor(): void
    {
        $test = (new TestBuilder)->build(new ReflectionClass(ModifiedConstructorTestCase::class), 'testCase');
        $this->assertInstanceOf(ModifiedConstructorTestCase::class, $test);
    }

    public function testCreateWithEmptyData(): void
    {
        $test = (new TestBuilder)->build(new ReflectionClass(EmptyDataProviderTest::class), 'testCase');
        $this->assertInstanceOf(DataProviderTestSuite::class, $test);
        /* @var DataProviderTestSuite $test */
        $this->assertInstanceOf(SkippedTestCase::class, $test->getGroupDetails()['default'][0]);
    }

    /**
     * @dataProvider provideWithAnnotations
     */
    public function testWithAnnotations(string $methodName): void
    {
        $test = (new TestBuilder)->build(new ReflectionClass(TestWithAnnotations::class), $methodName);
        $this->assertInstanceOf(TestWithAnnotations::class, $test);
    }

    public function provideWithAnnotations(): array
    {
        return [
            ['testThatInteractsWithGlobalVariables'],
            ['testThatInteractsWithStaticAttributes'],
            ['testInSeparateProcess'],
        ];
    }

    /**
     * @dataProvider provideWithAnnotationsAndDataProvider
     */
    public function testWithAnnotationsAndDataProvider(string $methodName): void
    {
        $test = (new TestBuilder)->build(new ReflectionClass(TestWithAnnotations::class), $methodName);
        $this->assertInstanceOf(DataProviderTestSuite::class, $test);
    }

    public function provideWithAnnotationsAndDataProvider(): array
    {
        return [
            ['testThatInteractsWithGlobalVariablesWithDataProvider'],
            ['testThatInteractsWithStaticAttributesWithDataProvider'],
            ['testInSeparateProcessWithDataProvider'],
        ];
    }
}
