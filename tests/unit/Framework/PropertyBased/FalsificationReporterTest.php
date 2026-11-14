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

use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Falsification\FalsifiedPropertiesTest;
use PHPUnit\TestFixture\PropertyBased\Falsification\FormattedCounterexamplesTest;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(FalsificationReporter::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class FalsificationReporterTest extends TestCase
{
    /**
     * @var list<array{skeleton: ?EventCollection, error: ?Throwable, falsification: ?FalsificationReport}>
     */
    private array $reports = [];

    #[WithoutErrorHandler]
    public function testShrinksTheCounterexampleOfAPropertyAndReportsHowItWasFalsified(): void
    {
        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedFromFive', 9);

        $this->shrinkAndReport($runner, 'testIsFalsifiedFromFive', $falsification, $originalFailure, 9);

        $report = $this->onlyReport();

        $this->assertNotNull($report['skeleton']);
        $this->assertNull($report['error']);

        $falsification = $report['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame(1234, $falsification->seed());
        $this->assertSame(3, $falsification->numberOfExamplesChecked());
        $this->assertSame([5], $falsification->counterexample()->choiceSequence());
        $this->assertSame(['value' => '5'], $falsification->counterexample()->arguments());
        $this->assertNull($falsification->counterexample()->explicitExample());
        $this->assertGreaterThan(0, $falsification->numberOfShrinkingSteps());
        $this->assertFalse($falsification->shrinkingInterrupted());
        $this->assertSame([], $falsification->otherFalsifications());
        $this->assertSame(4, $falsification->numberOfReplays());
        $this->assertSame(4, $falsification->numberOfReproductions());
    }

    #[WithoutErrorHandler]
    public function testReportsTheSimplestCounterexampleOfEachOtherWayInWhichThePropertyWasFalsifiedWhileItWasShrunk(): void
    {
        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedInTwoWays', 9);

        $this->shrinkAndReport($runner, 'testIsFalsifiedInTwoWays', $falsification, $originalFailure, 9);

        $falsification = $this->onlyReport()['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame(['value' => '5'], $falsification->counterexample()->arguments());
        $this->assertCount(1, $falsification->otherFalsifications());
        $this->assertStringStartsWith('RuntimeException at ', $falsification->otherFalsifications()[0]->description());
        $this->assertSame([3], $falsification->otherFalsifications()[0]->counterexample()->choiceSequence());
        $this->assertSame(['value' => '3'], $falsification->otherFalsifications()[0]->counterexample()->arguments());
    }

    #[WithoutErrorHandler]
    public function testReportsAPropertyAsFlakyWhenTheExampleThatFalsifiedItDoesNotFalsifyItAgain(): void
    {
        FalsifiedPropertiesTest::$numberOfRuns = 0;

        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedOnlyTheFirstTime', 9);

        $this->shrinkAndReport($runner, 'testIsFalsifiedOnlyTheFirstTime', $falsification, $originalFailure, 9);

        $report = $this->onlyReport();

        $this->assertNotNull($report['skeleton']);
        $this->assertNotNull($report['error']);
        $this->assertSame(FlakyPropertyException::class, $report['error']->className());
        $this->assertTrue($report['error']->hasPrevious());
        $this->assertSame(ExpectationFailedException::class, $report['error']->previous()->className());

        $falsification = $report['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame([9], $falsification->counterexample()->choiceSequence());
        $this->assertSame(['value' => '9'], $falsification->counterexample()->arguments());
        $this->assertSame(0, $falsification->numberOfShrinkingAttempts());
        $this->assertSame(4, $falsification->numberOfReplays());
        $this->assertSame(0, $falsification->numberOfReproductions());
    }

    #[WithoutErrorHandler]
    public function testDoesNotReportAPropertyWhenTheExampleThatFalsifiedItByTriggeringAnIssueDoesNotFalsifyItWhenItIsReplayed(): void
    {
        [$deriver, $generators] = $this->generators('testIsFalsifiedFromFive');

        $reported = $this->reporter($this->runner('testIsFalsifiedFromFive'))->shrinkAndReport(
            $deriver,
            $generators,
            null,
            Falsification::ofIssue('E_USER_WARNING', __FILE__, 1, 'message'),
            new ChoiceSequence([3]),
            3,
        );

        $this->assertFalse($reported);
        $this->assertSame([], $this->reports);
    }

    #[WithoutErrorHandler]
    public function testDoesNotReportAPropertyWhenTheExampleThatFalsifiedItByTriggeringAnIssueIsOnlyReplayedAfterShrinkingAndDoesNotFalsifyItThen(): void
    {
        [$deriver, $generators] = $this->generators('testIsFalsifiedFromFive');

        $reported = $this->reporter($this->runner('testIsFalsifiedFromFive'), true)->shrinkAndReport(
            $deriver,
            $generators,
            null,
            Falsification::ofIssue('E_USER_WARNING', __FILE__, 1, 'message'),
            new ChoiceSequence([3]),
            3,
        );

        $this->assertFalse($reported);
        $this->assertSame([], $this->reports);
    }

    #[WithoutErrorHandler]
    public function testShrinksAndReportsTheWayInWhichTheReplaysFalsifyThePropertyWhenTheyDoNotTriggerTheIssueThatFalsifiedIt(): void
    {
        [$deriver, $generators] = $this->generators('testIsFalsifiedFromFive');

        $reported = $this->reporter($this->runner('testIsFalsifiedFromFive'))->shrinkAndReport(
            $deriver,
            $generators,
            null,
            Falsification::ofIssue('E_USER_WARNING', __FILE__, 1, 'message'),
            new ChoiceSequence([9]),
            3,
        );

        $this->assertTrue($reported);

        $report = $this->onlyReport();

        $this->assertNotNull($report['skeleton']);
        $this->assertNull($report['error']);

        $falsification = $report['falsification'];

        $this->assertNotNull($falsification);
        $this->assertNull($falsification->failure());
        $this->assertSame(['value' => '5'], $falsification->counterexample()->arguments());
        $this->assertSame(4, $falsification->numberOfReplays());
        $this->assertSame(4, $falsification->numberOfReproductions());
    }

    #[WithoutErrorHandler]
    public function testReportsTheCounterexampleAsItIsWhenTheTestRunIsAborted(): void
    {
        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedFromFive', 9);
        [$deriver, $generators]                     = $this->generators('testIsFalsifiedFromFive');

        $this->reporter($runner, true)->shrinkAndReport($deriver, $generators, $originalFailure, $falsification, new ChoiceSequence([9]), 3);

        $falsification = $this->onlyReport()['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame(['value' => '9'], $falsification->counterexample()->arguments());
        $this->assertTrue($falsification->shrinkingInterrupted());
        $this->assertSame(0, $falsification->numberOfReplays());
    }

    #[WithoutErrorHandler]
    public function testReportsAPropertyThatWasFalsifiedByAnExplicitExampleWithItsArgumentsWithoutShrinkingThem(): void
    {
        $runner  = $this->runner('testIsFalsifiedFromFive');
        $example = $runner->example(['value' => 7], true);

        [$exampleEvents] = $runner->run($example, Facade::emitter(), false);

        $falsification = Falsification::of($example);

        $this->assertNotNull($falsification);

        $this->reporter($runner)->reportFalsificationByExplicitExample(
            $example,
            $falsification,
            $exampleEvents,
            new ExplicitExample('seven', ['value' => 7]),
            ['value' => '7'],
        );

        $report = $this->onlyReport();

        $this->assertSame($exampleEvents, $report['skeleton']);
        $this->assertNull($report['error']);

        $falsification = $report['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame([], $falsification->counterexample()->choiceSequence());
        $this->assertSame(['value' => '7'], $falsification->counterexample()->arguments());
        $this->assertSame('seven', $falsification->counterexample()->explicitExample());
        $this->assertSame(0, $falsification->numberOfExamplesChecked());
        $this->assertSame(0, $falsification->numberOfReplays());
    }

    #[WithoutErrorHandler]
    public function testCallsTheCounterexampleFormatterOnceForTheMinimalCounterexample(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;

        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedFromFive', 9, FormattedCounterexamplesTest::class);

        $this->shrinkAndReport($runner, 'testIsFalsifiedFromFive', $falsification, $originalFailure, 9, FormattedCounterexamplesTest::class);

        $falsification = $this->onlyReport()['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame('value 5, call 1', $falsification->counterexample()->formatted());
        $this->assertSame(1, FormattedCounterexamplesTest::$numberOfCalls);
    }

    #[WithoutErrorHandler]
    public function testCallsTheCounterexampleFormatterOnceForTheSimplestCounterexampleOfEachOtherWayInWhichThePropertyWasFalsified(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;

        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedInTwoWays', 9, FormattedCounterexamplesTest::class);

        $this->shrinkAndReport($runner, 'testIsFalsifiedInTwoWays', $falsification, $originalFailure, 9, FormattedCounterexamplesTest::class);

        $falsification = $this->onlyReport()['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame('value 5, call 1', $falsification->counterexample()->formatted());
        $this->assertCount(1, $falsification->otherFalsifications());
        $this->assertSame('value 3, call 2', $falsification->otherFalsifications()[0]->counterexample()->formatted());
        $this->assertSame(2, FormattedCounterexamplesTest::$numberOfCalls);
    }

    #[WithoutErrorHandler]
    public function testCallsTheCounterexampleFormatterOnceForTheCounterexampleOfAPropertyThatIsFlaky(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;
        FormattedCounterexamplesTest::$numberOfRuns  = 0;

        [$runner, $falsification, $originalFailure] = $this->falsify('testIsFalsifiedOnlyTheFirstTime', 9, FormattedCounterexamplesTest::class);

        $this->shrinkAndReport($runner, 'testIsFalsifiedOnlyTheFirstTime', $falsification, $originalFailure, 9, FormattedCounterexamplesTest::class);

        $report = $this->onlyReport();

        $this->assertNotNull($report['error']);
        $this->assertSame(FlakyPropertyException::class, $report['error']->className());
        $this->assertNotNull($report['falsification']);
        $this->assertSame('value 9, call 1', $report['falsification']->counterexample()->formatted());
        $this->assertSame(1, FormattedCounterexamplesTest::$numberOfCalls);
    }

    #[WithoutErrorHandler]
    public function testCallsTheCounterexampleFormatterWithTheArgumentsOfTheExplicitExampleThatFalsifiedTheProperty(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;

        $runner  = $this->runner('testIsFalsifiedFromFive', FormattedCounterexamplesTest::class);
        $example = $runner->example(['value' => 7], true);

        [$exampleEvents] = $runner->run($example, Facade::emitter(), false);

        $falsification = Falsification::of($example);

        $this->assertNotNull($falsification);

        $this->reporter($runner)->reportFalsificationByExplicitExample(
            $example,
            $falsification,
            $exampleEvents,
            new ExplicitExample('seven', ['value' => 7]),
            ['value' => '7'],
        );

        $falsification = $this->onlyReport()['falsification'];

        $this->assertNotNull($falsification);
        $this->assertSame('value 7, call 1', $falsification->counterexample()->formatted());
    }

    /**
     * @param non-empty-string       $methodName
     * @param non-negative-int       $value
     * @param class-string<TestCase> $className
     *
     * @return array{0: ExampleRunner, 1: Falsification, 2: ?Throwable}
     */
    private function falsify(string $methodName, int $value, string $className = FalsifiedPropertiesTest::class): array
    {
        $runner  = $this->runner($methodName, $className);
        $example = $runner->example(['value' => $value], false, DrawnValues::from(ChoiceSource::replay(new ChoiceSequence([$value]))));

        $runner->run($example, Facade::emitter(), false);

        $falsification = Falsification::of($example);
        $throwable     = ExampleRunner::propertyExampleOf($example)->throwableThatEndedIt();

        $this->assertNotNull($falsification);
        $this->assertNotNull($throwable);

        return [$runner, $falsification, ThrowableBuilder::from($throwable)];
    }

    /**
     * @param non-empty-string       $methodName
     * @param non-negative-int       $value
     * @param class-string<TestCase> $className
     */
    private function shrinkAndReport(ExampleRunner $runner, string $methodName, Falsification $falsification, ?Throwable $originalFailure, int $value, string $className = FalsifiedPropertiesTest::class): void
    {
        [$deriver, $generators] = $this->generators($methodName, $className);

        $this->reporter($runner)->shrinkAndReport($deriver, $generators, $originalFailure, $falsification, new ChoiceSequence([$value]), 3);
    }

    /**
     * @return array{skeleton: ?EventCollection, error: ?Throwable, falsification: ?FalsificationReport}
     */
    private function onlyReport(): array
    {
        $this->assertCount(1, $this->reports);

        return $this->reports[0];
    }

    private function reporter(ExampleRunner $runner, bool $shouldStop = false): FalsificationReporter
    {
        return new FalsificationReporter(
            $runner,
            new EventAggregator(Facade::emitter(), Facade::instance(), $this->valueObjectForEvents()),
            Facade::emitter(),
            1234,
            100,
            static fn (): bool => $shouldStop,
            function (?EventCollection $skeleton, ?Throwable $error, ?FalsificationReport $falsification): void
            {
                $this->reports[] = ['skeleton' => $skeleton, 'error' => $error, 'falsification' => $falsification];
            },
        );
    }

    /**
     * @param non-empty-string       $methodName
     * @param class-string<TestCase> $className
     */
    private function runner(string $methodName, string $className = FalsifiedPropertiesTest::class): ExampleRunner
    {
        return new ExampleRunner(
            new $className($methodName)->valueObjectForEvents(),
            false,
            static fn (): TestCase => new $className($methodName),
            new GeneratorResolver(new PropertyDefinitionBuilder()->build($className, $methodName)),
            Facade::instance(),
            FalsifyingIssues::fromConfiguration(ConfigurationRegistry::get()),
        );
    }

    /**
     * @param non-empty-string       $methodName
     * @param class-string<TestCase> $className
     *
     * @return array{0: Derivation\GeneratorDeriver, 1: array<non-empty-string, Generator\AbstractGenerator<mixed>>}
     */
    private function generators(string $methodName, string $className = FalsifiedPropertiesTest::class): array
    {
        return new GeneratorResolver(new PropertyDefinitionBuilder()->build($className, $methodName))->generators(new $className($methodName), $this->createStub(Emitter::class));
    }
}
