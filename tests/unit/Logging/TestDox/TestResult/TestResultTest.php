<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(TestResult::class)]
#[Group('testdox')]
#[Small]
final class TestResultTest extends TestCase
{
    public function test_Test_method_status_and_throwable_are_exposed_when_a_throwable_is_present(): void
    {
        $testMethod = $this->testMethod();
        $status     = TestStatus::error('boom');
        $throwable  = new Throwable(
            'RuntimeException',
            'boom',
            'RuntimeException: boom',
            '',
            null,
        );

        $result = new TestResult($testMethod, $status, $throwable);

        $this->assertSame($testMethod, $result->test());
        $this->assertSame($status, $result->status());
        $this->assertTrue($result->hasThrowable());
        $this->assertSame($throwable, $result->throwable());
    }

    public function test_Successful_result_has_no_throwable(): void
    {
        $result = new TestResult($this->testMethod(), TestStatus::success(), null);

        $this->assertFalse($result->hasThrowable());
        $this->assertNull($result->throwable());
    }

    private function testMethod(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
