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

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleTest;
use PHPUnit\TestRunner\TestResult\PassedTests;
use Throwable;

/**
 * Runs the lifecycle of tests that are examples of properties, with their
 * events collected rather than dispatched, so that they do not become part of
 * the test run that runs these tests.
 */
#[CoversClass(TestCase::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class TestCasePropertyExampleTest extends TestCase
{
    public function testATestIsNotAnExampleOfAPropertyByDefault(): void
    {
        $test = new ExampleTest('testRecordsItsArguments');

        $this->assertFalse($test->isPropertyExample());
    }

    public function testAnExampleIsRunWithTheGivenArguments(): void
    {
        $test = $this->example('testRecordsItsArguments', [42, 'a']);

        $this->runExample($test);

        $this->assertSame([42, 'a'], ExampleTest::$arguments);
        $this->assertTrue($test->status()->isSuccess());
    }

    public function testAnExampleHasTheIdentityOfItsTestMethod(): void
    {
        $test = $this->example('testRecordsItsArguments', [42, 'a']);

        $this->assertTrue($test->isPropertyExample());
        $this->assertFalse($test->usesDataProvider());
        $this->assertSame(ExampleTest::class . '::testRecordsItsArguments', $test->valueObjectForEvents()->id());
        $this->assertSame('testRecordsItsArguments', $test->nameWithDataSet());
    }

    public function testAnExampleThatPassesIsNotRegisteredAsPassed(): void
    {
        $test = $this->example('testRecordsItsArguments', [42, 'a']);

        $this->runExample($test);

        $this->assertTrue($test->status()->isSuccess());
        $this->assertFalse(PassedTests::instance()->hasTestMethodPassed(ExampleTest::class . '::testRecordsItsArguments'));
    }

    /**
     * @param non-empty-string $methodName
     * @param list<mixed>      $arguments
     */
    private function example(string $methodName, array $arguments): ExampleTest
    {
        $test = new ExampleTest($methodName);

        $test->setPropertyExample($arguments);

        return $test;
    }

    private function runExample(TestCase $test): void
    {
        $facade = EventFacade::instance();

        $facade->startCollectingEvents();

        try {
            $test->runLifecycle();
        } catch (Throwable) {
            // the lifecycle passes on the throwable that ended the test, which
            // the assertions of these tests inspect through the test
        } finally {
            $facade->stopCollectingEvents();
        }
    }
}
