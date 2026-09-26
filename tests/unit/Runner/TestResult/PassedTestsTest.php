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

    public function testForgetsRecordedPassesWhenReset(): void
    {
        $passedTests = new PassedTests;

        $passedTests->testClassPassed(self::class);

        $this->assertTrue($passedTests->hasTestClassPassed(self::class));

        $passedTests->reset();

        $this->assertFalse($passedTests->hasTestClassPassed(self::class));
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
}
