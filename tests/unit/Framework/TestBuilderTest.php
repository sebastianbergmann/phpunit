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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\TestFixture\TestWithAnnotations;
use ReflectionClass;

#[CoversClass(TestBuilder::class)]
final class TestBuilderTest extends TestCase
{
    public static function provideWithAnnotations(): array
    {
        return [
            ['testThatInteractsWithGlobalVariables'],
            ['testThatInteractsWithStaticAttributes'],
            ['testInSeparateProcess'],
        ];
    }

    public static function provideWithAnnotationsAndDataProvider(): array
    {
        return [
            ['testThatInteractsWithGlobalVariablesWithDataProvider'],
            ['testThatInteractsWithStaticAttributesWithDataProvider'],
            ['testInSeparateProcessWithDataProvider'],
        ];
    }

    #[DataProvider('provideWithAnnotations')]
    public function testWithAnnotations(string $methodName): void
    {
        $test = (new TestBuilder)->build(new ReflectionClass(TestWithAnnotations::class), $methodName);

        $this->assertInstanceOf(TestWithAnnotations::class, $test);
    }

    #[DataProvider('provideWithAnnotationsAndDataProvider')]
    public function testWithAnnotationsAndDataProvider(string $methodName): void
    {
        $test = (new TestBuilder)->build(new ReflectionClass(TestWithAnnotations::class), $methodName);

        $this->assertInstanceOf(DataProviderTestSuite::class, $test);
    }
}
