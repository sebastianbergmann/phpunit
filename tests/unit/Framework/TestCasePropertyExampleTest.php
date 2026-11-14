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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\ExampleRejectedByAssumptionException;
use PHPUnit\Framework\PropertyBased\PropertyExample;
use PHPUnit\TestFixture\PropertyBased\EventCollector;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleTest;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleWithAssumptionInTearDownTest;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleWithFailedAssertionInTearDownTest;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleWithFailingTearDownTest;
use PHPUnit\TestFixture\PropertyBased\Examples\ExampleWithOnNotSuccessfulTestTest;
use PHPUnit\TestFixture\PropertyBased\Examples\ObservingExampleTest;
use PHPUnit\TestFixture\PropertyBased\Examples\TargetingExampleTest;
use PHPUnit\TestRunner\TestResult\PassedTests;
use RuntimeException;
use Throwable;

/**
 * Runs the lifecycle of tests that are examples of properties, with their
 * events collected rather than dispatched, so that they do not become part of
 * the test run that runs these tests.
 */
#[CoversClass(TestCase::class)]
#[CoversClass(PropertyExample::class)]
#[CoversClass(ExampleRejectedByAssumptionException::class)]
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
            'exception in destructor of test double' => ['testUsesATestDoubleWhoseDestructorThrows', 'error', RuntimeException::class, 'destructor'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, non-empty-string}>
     */
    public static function invalidObservationProvider(): array
    {
        return [
            'empty label'       => ['testObservesAValueWithAnEmptyLabel', 'The label passed to observe() must not be empty'],
            'NAN'               => ['testObservesNan', 'The value passed to observe() must be finite'],
            'negative infinity' => ['testObservesInfinity', 'The value passed to observe() must be finite'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, non-empty-string}>
     */
    public static function invalidTargetProvider(): array
    {
        return [
            'empty label'      => ['testTargetsAScoreWithAnEmptyLabel', 'The label passed to target() must not be empty'],
            'NAN'              => ['testTargetsNan', 'The score passed to target() must be finite'],
            'infinity'         => ['testTargetsInfinity', 'The score passed to target() must be finite'],
            'label used twice' => ['testTargetsAScoreWithTheSameLabelTwice', 'target() can only be used once per example with the label "size"'],
        ];
    }

    public function testATestIsNotAnExampleOfAPropertyByDefault(): void
    {
        $test = new ExampleTest('testRecordsItsArguments');

        $this->assertNull($test->propertyExample());
    }

    public function testAnExampleIsRunWithTheGivenArguments(): void
    {
        $test = $this->example('testRecordsItsArguments', [42, 'a']);

        $this->runExample($test);

        $this->assertSame([42, 'a'], ExampleTest::$arguments);
        $this->assertTrue($test->status()->isSuccess());
        $this->assertNull($this->propertyExampleOf($test)->throwableThatEndedIt());
    }

    public function testAnExampleHasTheIdentityOfItsTestMethod(): void
    {
        $test = $this->example('testRecordsItsArguments', [42, 'a']);

        $this->assertNotNull($test->propertyExample());
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

    public function testAnExampleIsRejectedWhenAssumeIsCalledWithFalse(): void
    {
        $test = $this->example('testRejectsTheExample', [1]);

        $this->runExample($test);

        $this->assertTrue($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isUnknown());
        $this->assertNull($this->propertyExampleOf($test)->throwableThatEndedIt());
    }

    public function testAnExampleIsRejectedWhenAssumeIsCalledWithFalseWhileAnExceptionIsExpected(): void
    {
        $test = $this->example('testRejectsTheExampleWhileAnExceptionIsExpected', [1]);

        $this->runExample($test);

        $this->assertTrue($this->propertyExampleOf($test)->wasRejected());
    }

    public function testAnExampleIsRejectedWhenAssumeIsCalledWithFalseWhileAnExceptionOfPHPUnitIsExpected(): void
    {
        $test = $this->example('testRejectsTheExampleWhileAnExceptionOfPHPUnitIsExpected', [1]);

        $this->runExample($test);

        $this->assertTrue($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isUnknown());
    }

    public function testAnExampleThatCalledAssumeWithFalseIsNotRejectedWhenTearDownErrors(): void
    {
        $test = new ExampleWithFailingTearDownTest('testRejectsTheExample');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertFalse($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isError());
        $this->assertInstanceOf(RuntimeException::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
        $this->assertSame('tearDown', $this->propertyExampleOf($test)->throwableThatEndedIt()->getMessage());
    }

    public function testAnExampleThatCalledAssumeWithFalseIsNotRejectedWhenAnAssertionInTearDownFails(): void
    {
        $test = new ExampleWithFailedAssertionInTearDownTest('testRejectsTheExample');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertFalse($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isFailure());
        $this->assertInstanceOf(AssertionFailedError::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
        $this->assertSame('tearDown', $this->propertyExampleOf($test)->throwableThatEndedIt()->getMessage());
    }

    public function testAnExampleThatCalledAssumeWithFalseIsNotRejectedWhenTheDestructorOfATestDoubleErrors(): void
    {
        $test = $this->example('testRejectsTheExampleAndUsesATestDoubleWhoseDestructorThrows', [1]);

        $this->runExample($test);

        $this->assertFalse($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isError());
        $this->assertInstanceOf(RuntimeException::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
        $this->assertSame('destructor', $this->propertyExampleOf($test)->throwableThatEndedIt()->getMessage());
    }

    public function testAnExampleIsRejectedWhenAssumeIsCalledWithFalseInTearDown(): void
    {
        $test = new ExampleWithAssumptionInTearDownTest('testPasses');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertTrue($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isUnknown());
        $this->assertNull($this->propertyExampleOf($test)->throwableThatEndedIt());
    }

    public function testAnExampleThatIsRejectedIsNotAnErrorWhenAssumeIsCalledWithFalseInTearDown(): void
    {
        $test = new ExampleWithAssumptionInTearDownTest('testRejectsTheExample');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertTrue($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isUnknown());
        $this->assertNull($this->propertyExampleOf($test)->throwableThatEndedIt());
    }

    public function testAnExampleThatFailsIsNotRejectedWhenAssumeIsCalledWithFalseInTearDown(): void
    {
        $test = new ExampleWithAssumptionInTearDownTest('testFails');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertFalse($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isFailure());
        $this->assertInstanceOf(AssertionFailedError::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
    }

    public function testAnExampleThatFailsWithAThrowableOfARegisteredFailureTypeIsNotRejectedWhenAssumeIsCalledWithFalseInTearDown(): void
    {
        $test = new ExampleWithAssumptionInTearDownTest('testFailsWithAThrowableOfARegisteredFailureType');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertFalse($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isFailure());
        $this->assertInstanceOf(DomainException::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
    }

    public function testKeepsTheThrowableOfAFailedAssertionInTearDown(): void
    {
        $test = new ExampleWithFailedAssertionInTearDownTest('testPasses');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertTrue($test->status()->isFailure());
        $this->assertInstanceOf(AssertionFailedError::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
        $this->assertSame('tearDown', $this->propertyExampleOf($test)->throwableThatEndedIt()->getMessage());
    }

    public function testAnExampleIsNotRejectedWhenAssumeIsCalledWithTrue(): void
    {
        $test = $this->example('testAcceptsTheExample', [1]);

        $this->runExample($test);

        $this->assertFalse($this->propertyExampleOf($test)->wasRejected());
        $this->assertTrue($test->status()->isSuccess());
    }

    public function testAnExampleIsClassifiedWithEachLabelOnce(): void
    {
        $test = $this->example('testClassifiesTheExample', [1]);

        $this->runExample($test);

        $this->assertSame(['first', 'second'], $this->propertyExampleOf($test)->classifications());
    }

    public function testAnExampleCannotBeClassifiedWithAnEmptyLabel(): void
    {
        $test = $this->example('testClassifiesTheExampleWithAnEmptyLabel', [1]);

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame('The label passed to classify() must not be empty', $test->status()->message());
    }

    public function testClassifyCanOnlyBeUsedInAProperty(): void
    {
        $test = new ExampleTest('testUsesClassifyOutsideOfAProperty');

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame('classify() can only be used in a property', $test->status()->message());
    }

    public function testAnExampleObservesValuesWithTheirLabels(): void
    {
        $test = $this->observingExample('testObservesValues');

        $this->runExample($test);

        $this->assertTrue($test->status()->isSuccess());
        $this->assertSame(
            [
                ['label' => 'size', 'value' => 3],
                ['label' => 'ratio', 'value' => 0.5],
                ['label' => 'size', 'value' => 6],
            ],
            $this->propertyExampleOf($test)->observations(),
        );
    }

    /**
     * @param non-empty-string $methodName
     * @param non-empty-string $message
     */
    #[DataProvider('invalidObservationProvider')]
    public function testAnExampleCannotObserveAValueWithAnEmptyLabelOrAValueThatIsNotFinite(string $methodName, string $message): void
    {
        $test = $this->observingExample($methodName);

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame($message, $test->status()->message());
        $this->assertSame([], $this->propertyExampleOf($test)->observations());
    }

    public function testObserveCanOnlyBeUsedInAProperty(): void
    {
        $test = new ObservingExampleTest('testUsesObserveOutsideOfAProperty');

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame('observe() can only be used in a property', $test->status()->message());
    }

    public function testAnExampleTargetsScoresWithTheirLabels(): void
    {
        $test = $this->targetingExample('testTargetsScores');

        $this->runExample($test);

        $this->assertTrue($test->status()->isSuccess());
        $this->assertSame(['size' => 3, 'ratio' => 0.5], $this->propertyExampleOf($test)->targets());
    }

    /**
     * @param non-empty-string $methodName
     * @param non-empty-string $message
     */
    #[DataProvider('invalidTargetProvider')]
    public function testAnExampleCannotTargetAScoreWithAnEmptyLabelAScoreThatIsNotFiniteOrTwoScoresWithTheSameLabel(string $methodName, string $message): void
    {
        $test = $this->targetingExample($methodName);

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame($message, $test->status()->message());
    }

    public function testTargetCanOnlyBeUsedInAProperty(): void
    {
        $test = new TargetingExampleTest('testUsesTargetOutsideOfAProperty');

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame('target() can only be used in a property', $test->status()->message());
    }

    public function testAssumeCanOnlyBeUsedInAProperty(): void
    {
        $test = new ExampleTest('testUsesAssumeOutsideOfAProperty');

        $this->runExample($test);

        $this->assertTrue($test->status()->isError());
        $this->assertSame('assume() can only be used in a property', $test->status()->message());
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

        $this->assertInstanceOf($throwableClassName, $this->propertyExampleOf($test)->throwableThatEndedIt());
        $this->assertSame($message, $this->propertyExampleOf($test)->throwableThatEndedIt()->getMessage());
    }

    public function testOnNotSuccessfulTestIsNotCalledForAnExampleThatIsRejected(): void
    {
        ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled = false;

        $test = new ExampleWithOnNotSuccessfulTestTest('testRejectsTheExample');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertTrue($this->propertyExampleOf($test)->wasRejected());
        $this->assertFalse(ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled);
    }

    public function testOnNotSuccessfulTestIsNotCalledForAnExampleThatFalsifiesThePropertyWithoutBeingReported(): void
    {
        ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled = false;

        $test = new ExampleWithOnNotSuccessfulTestTest('testFails');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertTrue($test->status()->isFailure());
        $this->assertInstanceOf(AssertionFailedError::class, $this->propertyExampleOf($test)->throwableThatEndedIt());
        $this->assertFalse(ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled);
    }

    public function testOnNotSuccessfulTestIsCalledForAnExampleWhoseFailureIsReportedForTheProperty(): void
    {
        ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled = false;

        $test = new ExampleWithOnNotSuccessfulTestTest('testFails');

        $test->setPropertyExample(new PropertyExample([1], true, null));

        $this->runExample($test);

        $this->assertTrue($test->status()->isFailure());
        $this->assertTrue(ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled);
    }

    public function testOnNotSuccessfulTestIsCalledForAnExampleThatIsSkipped(): void
    {
        ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled = false;

        $test = new ExampleWithOnNotSuccessfulTestTest('testIsSkipped');

        $test->setPropertyExample(new PropertyExample([1], false, null));

        $this->runExample($test);

        $this->assertTrue($test->status()->isSkipped());
        $this->assertTrue(ExampleWithOnNotSuccessfulTestTest::$onNotSuccessfulTestCalled);
    }

    /**
     * @param non-empty-string $methodName
     * @param list<mixed>      $arguments
     */
    private function example(string $methodName, array $arguments): ExampleTest
    {
        $test = new ExampleTest($methodName);

        $test->setPropertyExample(new PropertyExample($arguments, false, null));

        return $test;
    }

    /**
     * @param non-empty-string $methodName
     */
    private function observingExample(string $methodName): ObservingExampleTest
    {
        $test = new ObservingExampleTest($methodName);

        $test->setPropertyExample(new PropertyExample([3], false, null));

        return $test;
    }

    /**
     * @param non-empty-string $methodName
     */
    private function targetingExample(string $methodName): TargetingExampleTest
    {
        $test = new TargetingExampleTest($methodName);

        $test->setPropertyExample(new PropertyExample([3], false, null));

        return $test;
    }

    private function propertyExampleOf(TestCase $test): PropertyExample
    {
        $propertyExample = $test->propertyExample();

        $this->assertNotNull($propertyExample);

        return $propertyExample;
    }

    private function runExample(TestCase $test): void
    {
        EventCollector::collect(
            static function () use ($test): void
            {
                try {
                    $test->runLifecycle();
                } catch (Throwable) {
                    // the lifecycle passes on the throwable that ended the
                    // test, which the assertions of these tests inspect
                    // through the test
                }
            },
        );
    }
}
