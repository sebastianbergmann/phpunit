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

use DomainException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleTest;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleWithFailedAssertionInTearDownTest;
use PHPUnit\TestRunner\TestResult\PassedTests;
use RuntimeException;
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
    /**
     * @return array<string, array{non-empty-string, 'error'|'failure', class-string<Throwable>, string}>
     */
    public static function throwableProvider(): array
    {
        return [
            'failed assertion'                       => ['testFails', 'failure', AssertionFailedError::class, 'failure'],
            'exception'                              => ['testErrors', 'error', RuntimeException::class, 'error'],
            'unmet expectation for a test double'    => ['testDoesNotMeetTheExpectationForATestDouble', 'failure', ExpectationFailedException::class, 'Countable::count() was expected to be invoked once but was never invoked.'],
            'unmet expectation for the output'       => ['testDoesNotMeetTheExpectationForItsOutput', 'failure', ExpectationFailedException::class, 'Failed asserting that two strings are identical.'],
            'throwable of a registered failure type' => ['testFailsWithAThrowableOfARegisteredFailureType', 'failure', DomainException::class, 'registered'],
        ];
    }

    public function testATestIsNotAnExampleOfAPropertyByDefault(): void
    {
        $test = new ExampleTest('testRecordsItsArguments');

        $this->assertFalse($test->isPropertyExample());
        $this->assertNull($test->throwableThatEndedTheTest());
    }

    public function testAnExampleIsRunWithTheGivenArguments(): void
    {
        $test = $this->example('testRecordsItsArguments', [42, 'a']);

        $this->runExample($test);

        $this->assertSame([42, 'a'], ExampleTest::$arguments);
        $this->assertTrue($test->status()->isSuccess());
        $this->assertNull($test->throwableThatEndedTheTest());
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

    public function testKeepsTheThrowableOfAFailedAssertionInTearDown(): void
    {
        $test = new ExampleWithFailedAssertionInTearDownTest('testPasses');

        $test->setPropertyExample([1]);

        $this->runExample($test);

        $this->assertTrue($test->status()->isFailure());
        $this->assertInstanceOf(AssertionFailedError::class, $test->throwableThatEndedTheTest());
        $this->assertSame('tearDown', $test->throwableThatEndedTheTest()->getMessage());
    }

    public function testKeepsTheThrowableThatIsRaisedWhenATestDoubleIsDestructed(): void
    {
        $test = $this->example('testUsesATestDoubleWhoseDestructorThrows', [1]);

        $this->runExample($test);

        $this->assertInstanceOf(RuntimeException::class, $test->throwableThatEndedTheTest());
        $this->assertSame('destructor', $test->throwableThatEndedTheTest()->getMessage());
    }

    /**
     * @param non-empty-string        $methodName
     * @param 'error'|'failure'       $status
     * @param class-string<Throwable> $throwableClassName
     */
    #[DataProvider('throwableProvider')]
    public function testKeepsTheThrowableThatEndedTheExampleAsItWasRaised(string $methodName, string $status, string $throwableClassName, string $message): void
    {
        $test = $this->example($methodName, [1]);

        $this->runExample($test);

        if ($status === 'failure') {
            $this->assertTrue($test->status()->isFailure());
        } else {
            $this->assertTrue($test->status()->isError());
        }

        $this->assertInstanceOf($throwableClassName, $test->throwableThatEndedTheTest());
        $this->assertSame($message, $test->throwableThatEndedTheTest()->getMessage());
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
