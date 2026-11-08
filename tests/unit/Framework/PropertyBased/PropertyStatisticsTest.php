<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use const PHP_INT_MAX;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ExampleRun;
use PHPUnit\TestFixture\PropertyBased\StateMachine\StuckMachine;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(PropertyStatistics::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class PropertyStatisticsTest extends TestCase
{
    public function testGeneratesAtMostTenTimesTheNumberOfExamples(): void
    {
        $this->assertSame(100, $this->statistics(10)->maximumNumberOfExamplesGenerated());
    }

    public function testTheMaximumNumberOfExamplesGeneratedDoesNotExceedTheLargestInteger(): void
    {
        $this->assertSame(PHP_INT_MAX, $this->statistics(PHP_INT_MAX)->maximumNumberOfExamplesGenerated());
    }

    public function testCountsTheGeneratedExamplesThatWereChecked(): void
    {
        $statistics = $this->statistics(4);

        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertSame(1, $statistics->numberOfExamplesChecked());
        $this->assertFalse($statistics->halfOfTheExamplesWereChecked());
        $this->assertFalse($statistics->allExamplesWereChecked());

        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertTrue($statistics->halfOfTheExamplesWereChecked());
        $this->assertFalse($statistics->allExamplesWereChecked());

        $statistics->recordCheckedGeneratedExample($this->example());
        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertSame(4, $statistics->numberOfExamplesChecked());
        $this->assertTrue($statistics->allExamplesWereChecked());
    }

    public function testCountsTheExplicitExamplesThatWereCheckedSeparately(): void
    {
        $statistics = $this->statistics(4);

        $statistics->recordCheckedExplicitExample();

        $this->assertSame(1, $statistics->numberOfExplicitExamplesChecked());
        $this->assertSame(0, $statistics->numberOfExamplesChecked());
    }

    public function testCountsTheClassificationsOfTheExamplesThatWereCheckedInTheOrderInWhichTheyWereFirstUsed(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordCheckedGeneratedExample($this->example('small'));
        $statistics->recordCheckedGeneratedExample($this->example('large', 'even'));
        $statistics->recordCheckedGeneratedExample($this->example('small', 'even'));

        $this->assertSame(
            [
                ['label' => 'small', 'numberOfExamples' => 2],
                ['label' => 'large', 'numberOfExamples' => 1],
                ['label' => 'even', 'numberOfExamples' => 2],
            ],
            $statistics->classifications(),
        );
    }

    public function testRecordsTheValuesThatTheExamplesThatWereCheckedObserved(): void
    {
        $statistics = $this->statistics(10);

        $first = $this->example();
        $first->observe('size', 1);

        $second = $this->example();
        $second->observe('size', 3);

        $statistics->recordCheckedGeneratedExample($first);
        $statistics->recordCheckedGeneratedExample($second);

        $this->assertSame(
            [
                [
                    'label'          => 'size',
                    'numberOfValues' => 2,
                    'minimum'        => 1,
                    'median'         => 1,
                    'mean'           => 2.0,
                    'percentile90'   => 3,
                    'maximum'        => 3,
                ],
            ],
            $statistics->observations(),
        );
    }

    public function testCountsTheExamplesThatWereRejected(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRejectedExample(RejectionReason::Assumption);
        $statistics->recordRejectedExample(RejectionReason::FilterNotSatisfied);

        $this->assertSame(2, $statistics->numberOfExamplesRejected());
    }

    public function testExamplesAreGeneratedAsLongAsNoneWasRejected(): void
    {
        $this->assertFalse($this->statistics(10, 5, 1)->tooManyExamplesWereRejected());
    }

    public function testExamplesAreNoLongerGeneratedOnceTooManyWereRejectedBeforeEnoughWereChecked(): void
    {
        $statistics = $this->statistics(10, 5, 2);

        $statistics->recordRejectedExample(RejectionReason::Assumption);

        $this->assertFalse($statistics->tooManyExamplesWereRejected());

        $statistics->recordRejectedExample(RejectionReason::FilterNotSatisfied);
        $statistics->recordRejectedExample(RejectionReason::FilterNotSatisfied);

        $this->assertTrue($statistics->tooManyExamplesWereRejected());
        $this->assertSame(
            [
                'This property was checked for only 0 of 10 examples because generating examples was stopped after 3 examples were rejected (2 because no value that satisfies a filter was generated, 1 by assume()) while fewer than 5 examples were checked; generate the values that the property needs instead of rejecting the others',
            ],
            $statistics->riskyMessages(false, []),
        );
    }

    public function testExamplesAreNoLongerGeneratedOnceTooManyWereTooLargeBeforeEnoughWereChecked(): void
    {
        $statistics = $this->statistics(10, 5, 10, 2);

        $statistics->recordRejectedExample(RejectionReason::Assumption);
        $statistics->recordRejectedExample(RejectionReason::ObjectsNestedTooDeeply);
        $statistics->recordRejectedExample(RejectionReason::TooManyChoices);
        $statistics->recordRejectedExample(RejectionReason::TooManyChoices);

        $this->assertTrue($statistics->tooManyExamplesWereRejected());
        $this->assertSame(
            [
                'This property was checked for only 0 of 10 examples because generating examples was stopped after 4 examples were rejected (2 because generating them required too many choices, 1 by assume(), 1 because generating them required too many nested objects) while fewer than 5 examples were checked; generate smaller values',
            ],
            $statistics->riskyMessages(false, []),
        );
    }

    public function testExamplesAreGeneratedRegardlessOfHowManyWereRejectedOnceEnoughWereChecked(): void
    {
        $statistics = $this->statistics(10, 1, 1);

        $statistics->recordCheckedGeneratedExample($this->example());
        $statistics->recordRejectedExample(RejectionReason::Assumption);

        $this->assertFalse($statistics->tooManyExamplesWereRejected());
    }

    public function testAPropertyForWhichTheMaximumNumberOfExamplesWasGeneratedWithoutCheckingEnoughIsRisky(): void
    {
        $statistics = $this->statistics(1);

        $statistics->recordCheckedExplicitExample();

        for ($i = 0; $i < 10; $i++) {
            $statistics->recordRejectedExample(RejectionReason::Assumption);
        }

        $this->assertSame(
            [
                'This property was checked for only 0 of 1 generated examples (and 1 explicit example), 10 examples were rejected by assume(); generate the values that the property needs instead of rejecting the others',
            ],
            $statistics->riskyMessages(false, []),
        );
    }

    public function testAPropertyForWhichEveryExampleWasGeneratedAndRejectedIsRisky(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordCheckedExplicitExample();
        $statistics->recordCheckedExplicitExample();
        $statistics->recordRejectedExample(RejectionReason::Assumption);
        $statistics->recordThatEveryExampleWasGenerated();

        $this->assertSame(
            [
                'This property was checked for only 0 of 10 generated examples (and 2 explicit examples), 1 example was rejected by assume(); generate the values that the property needs instead of rejecting the others',
            ],
            $statistics->riskyMessages(false, []),
        );
    }

    public function testAPropertyWhoseCheckWasAbortedIsRisky(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordCheckedGeneratedExample($this->example());
        $statistics->recordThatTheTestRunWasAborted();

        $this->assertSame(
            ['This property was checked for only 1 of 10 examples because the test run was aborted'],
            $statistics->riskyMessages(false, []),
        );
    }

    public function testAPropertyThatPassedIsRiskyWhenFewerOfItsExamplesThanRequiredWereClassifiedWithALabel(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRunOfExample(new ExampleRun(1, false, null));
        $statistics->recordCheckedGeneratedExample($this->example('zero'));
        $statistics->recordRunOfExample(new ExampleRun(1, false, null));
        $statistics->recordCheckedGeneratedExample($this->example());

        $requirements = [
            ['label' => 'zero', 'minimumPercentage' => 50.0],
            ['label' => 'negative', 'minimumPercentage' => 10.0],
        ];

        $this->assertSame(
            ['Only 0.0% of the examples of this property were classified as "negative", but at least 10.0% are required'],
            $statistics->riskyMessages(true, $requirements),
        );
        $this->assertSame([], $statistics->riskyMessages(false, $requirements));
    }

    public function testAPropertyThatPassedWithoutPerformingAssertionsIsRisky(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRunOfExample(new ExampleRun(0, false, null));
        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertSame(['This test did not perform any assertions'], $statistics->riskyMessages(true, []));
        $this->assertSame(0, $statistics->numberOfAssertions());
    }

    public function testCountsTheAssertionsOfTheExamplesThatRan(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRunOfExample(new ExampleRun(2, false, null));
        $statistics->recordRunOfExample(new ExampleRun(3, false, null));
        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertSame(5, $statistics->numberOfAssertions());
        $this->assertSame([], $statistics->riskyMessages(true, []));
    }

    public function testAPropertyThatIsNotExpectedToPerformAssertionsButPerformedSomeIsRisky(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRunOfExample(new ExampleRun(0, true, null));
        $statistics->recordRunOfExample(new ExampleRun(1, false, null));

        $this->assertSame(
            ['This test is not expected to perform assertions but performed 1 assertion'],
            $statistics->riskyMessages(false, []),
        );
    }

    public function testAPropertyThatPassedIsRiskyWhenNoneOfItsExamplesContributedToCodeCoverage(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRunOfExample(new ExampleRun(1, false, false));
        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertSame(['This test does not contribute to code coverage'], $statistics->riskyMessages(true, []));

        $statistics->recordContributionToCodeCoverage(true);
        $statistics->recordContributionToCodeCoverage(false);

        $this->assertSame([], $statistics->riskyMessages(true, []));
    }

    public function testAPropertyThatPassedIsRiskyWhenTheStateMachineOfMostOfItsExamplesTookNoStep(): void
    {
        $statistics = $this->statistics(10);

        $statistics->recordRunOfExample(new ExampleRun(1, false, null));
        $statistics->recordCheckedGeneratedExample($this->exampleThatRanAStuckStateMachine());
        $statistics->recordCheckedGeneratedExample($this->example());

        $this->assertSame(
            ['The state machine of this property took no step in 1 of 1 examples because none of its rules could be applied; make sure that the preconditions of the rules hold and that values are produced for the pools from which they take values'],
            $statistics->riskyMessages(true, []),
        );
        $this->assertSame([], $statistics->riskyMessages(false, []));
    }

    /**
     * @param positive-int     $numberOfExamples
     * @param non-negative-int $examplesCheckedUntilHealthIsAssumed
     * @param positive-int     $maximumRejected
     * @param positive-int     $maximumTooLarge
     */
    private function statistics(int $numberOfExamples, int $examplesCheckedUntilHealthIsAssumed = 5, int $maximumRejected = 100, int $maximumTooLarge = 100): PropertyStatistics
    {
        return new PropertyStatistics($numberOfExamples, $examplesCheckedUntilHealthIsAssumed, $maximumRejected, $maximumTooLarge, true);
    }

    /**
     * @param non-empty-string ...$labels
     */
    private function example(string ...$labels): PropertyExample
    {
        $example = new PropertyExample([], false, null);

        foreach ($labels as $label) {
            $example->classify($label);
        }

        return $example;
    }

    private function exampleThatRanAStuckStateMachine(): PropertyExample
    {
        $example = new PropertyExample([], false, DrawnValues::from(ChoiceSource::replay(new ChoiceSequence([]))));

        $example->runStateMachine(new StuckMachine, 5);

        return $example;
    }
}
