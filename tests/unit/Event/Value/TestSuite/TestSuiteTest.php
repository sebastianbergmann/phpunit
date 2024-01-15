<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestSuite::class)]
#[CoversClass(TestSuiteForTestClass::class)]
#[CoversClass(TestSuiteForTestMethodWithDataProvider::class)]
#[CoversClass(TestSuiteWithName::class)]
#[Small]
final class TestSuiteTest extends TestCase
{
    public function testCanBeTestSuiteForTestClass(): void
    {
        $className = 'ExampleTest';
        $size      = 0;
        $tests     = TestCollection::fromArray([]);
        $file      = 'ExampleTest.php';
        $line      = 1;

        $testSuite = new TestSuiteForTestClass($className, $size, $tests, $file, $line);

        $this->assertTrue($testSuite->isForTestClass());
        $this->assertFalse($testSuite->isForTestMethodWithDataProvider());
        $this->assertFalse($testSuite->isWithName());

        $this->assertSame($className, $testSuite->className());
        $this->assertSame($className, $testSuite->name());
        $this->assertSame($size, $testSuite->count());
        $this->assertSame($tests, $testSuite->tests());
        $this->assertSame($file, $testSuite->file());
        $this->assertSame($line, $testSuite->line());
    }

    public function testCanBeTestSuiteForTestMethodWithDataProvider(): void
    {
        $name       = 'ExampleTest::testOne';
        $className  = 'ExampleTest';
        $methodName = 'testOne';
        $size       = 0;
        $tests      = TestCollection::fromArray([]);
        $file       = 'ExampleTest.php';
        $line       = 1;

        $testSuite = new TestSuiteForTestMethodWithDataProvider($name, $size, $tests, $className, $methodName, $file, $line);

        $this->assertFalse($testSuite->isForTestClass());
        $this->assertTrue($testSuite->isForTestMethodWithDataProvider());
        $this->assertFalse($testSuite->isWithName());

        $this->assertSame($name, $testSuite->name());
        $this->assertSame($className, $testSuite->className());
        $this->assertSame($methodName, $testSuite->methodName());
        $this->assertSame($size, $testSuite->count());
        $this->assertSame($tests, $testSuite->tests());
        $this->assertSame($file, $testSuite->file());
        $this->assertSame($line, $testSuite->line());
    }

    public function testCanBeTestSuiteWithName(): void
    {
        $name  = 'the-name';
        $size  = 0;
        $tests = TestCollection::fromArray([]);

        $testSuite = new TestSuiteWithName($name, $size, $tests);

        $this->assertFalse($testSuite->isForTestClass());
        $this->assertFalse($testSuite->isForTestMethodWithDataProvider());
        $this->assertTrue($testSuite->isWithName());

        $this->assertSame($name, $testSuite->name());
        $this->assertSame($size, $testSuite->count());
        $this->assertSame($tests, $testSuite->tests());
    }
}
