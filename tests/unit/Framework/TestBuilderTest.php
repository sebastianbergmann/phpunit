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

use function assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\TestFixture\EmptyDataProviderTest;
use PHPUnit\TestFixture\TestWithAnnotations;
use ReflectionClass;

/**
 * @covers \PHPUnit\Framework\TestBuilder
 */
final class TestBuilderTest extends TestCase
{
    public function testCreateTestForNotInstantiableTestClass(): void
    {
        $reflector = $this->getMockBuilder(ReflectionClass::class)
            ->setConstructorArgs([$this])
            ->getMock();

        assert($reflector instanceof MockObject);
        assert($reflector instanceof ReflectionClass);

        $reflector->expects($this->once())
            ->method('isInstantiable')
            ->willReturn(false);

        $reflector->expects($this->once())
            ->method('getName')
            ->willReturn('foo');

        $test = (new TestBuilder)->build($reflector, 'TestForNonInstantiableTestClass');

        $this->assertInstanceOf(ErrorTestCase::class, $test);

        /* @var ErrorTestCase $test */
        $this->assertSame('Cannot instantiate class "foo".', $test->getMessage());
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
