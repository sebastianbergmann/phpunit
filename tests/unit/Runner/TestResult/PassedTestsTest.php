<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture\HookFixture;

#[CoversClass(PassedTests::class)]
#[Small]
#[Group('test-runner')]
final class PassedTestsTest extends TestCase
{
    public function testIsNotGreaterThanUnknownSize(): void
    {
        $passedTests = new PassedTests;

        $passedTests->testMethodPassed($this->testMethod(), null);

        $this->assertFalse($passedTests->isGreaterThan(HookFixture::class . '::testOne', TestSize::unknown()));
    }

    public function testIsNotGreaterThanForTestMethodThatDidNotPass(): void
    {
        $this->assertFalse((new PassedTests)->isGreaterThan(HookFixture::class . '::testOne', TestSize::small()));
    }

    public function testIsNotGreaterThanForPassedTestMethodOfUnknownSize(): void
    {
        $passedTests = new PassedTests;

        $passedTests->testMethodPassed($this->testMethod(), null);

        $this->assertFalse($passedTests->isGreaterThan(HookFixture::class . '::testOne', TestSize::small()));
    }

    public function testHasNoReturnValueForTestMethodThatDidNotPass(): void
    {
        $this->assertNull((new PassedTests)->returnValue(HookFixture::class . '::testOne'));
    }

    public function testImportsTheTestClassesAndTestMethodsThatAnotherInstanceRecordedAsPassed(): void
    {
        $passedTests = new PassedTests;

        $passedTests->testClassPassed(self::class);
        $passedTests->testMethodPassed($this->testMethodOfThisTestClass('testImportsTheTestClassesAndTestMethodsThatAnotherInstanceRecordedAsPassed'), 'recorded');

        $other = new PassedTests;

        $other->testClassPassed(TestCase::class);
        $other->testMethodPassed($this->testMethodOfThisTestClass('testReplacesThePassOfATestMethodWithTheImportedOne'), 'imported');

        $passedTests->import($other);

        $this->assertTrue($passedTests->hasTestClassPassed(self::class));
        $this->assertTrue($passedTests->hasTestClassPassed(TestCase::class));
        $this->assertSame('recorded', $passedTests->returnValue(self::class . '::testImportsTheTestClassesAndTestMethodsThatAnotherInstanceRecordedAsPassed'));
        $this->assertSame('imported', $passedTests->returnValue(self::class . '::testReplacesThePassOfATestMethodWithTheImportedOne'));
    }

    public function testReplacesThePassOfATestMethodWithTheImportedOne(): void
    {
        $passedTests = new PassedTests;

        $passedTests->testMethodPassed($this->testMethodOfThisTestClass('testReplacesThePassOfATestMethodWithTheImportedOne'), 'recorded');

        $other = new PassedTests;

        $other->testMethodPassed($this->testMethodOfThisTestClass('testReplacesThePassOfATestMethodWithTheImportedOne'), 'imported');

        $passedTests->import($other);

        $this->assertSame('imported', $passedTests->returnValue(self::class . '::testReplacesThePassOfATestMethodWithTheImportedOne'));
    }

    private function testMethod(): TestMethod
    {
        return new TestMethod(
            HookFixture::class,
            'testOne',
            'HookFixture.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName(HookFixture::class, 'testOne'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    /**
     * @param non-empty-string $methodName
     */
    private function testMethodOfThisTestClass(string $methodName): TestMethod
    {
        return new TestMethod(
            self::class,
            $methodName,
            __FILE__,
            1,
            TestDoxBuilder::fromClassNameAndMethodName(self::class, $methodName),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
