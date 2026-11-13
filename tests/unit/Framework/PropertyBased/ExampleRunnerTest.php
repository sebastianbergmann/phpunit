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

use LogicException;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(ExampleRunner::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ExampleRunnerTest extends TestCase
{
    public function testCreatesTheTestCaseOfAnExampleWithItsArguments(): void
    {
        $example         = $this->runner('testWithExplicitExamples')->example(['small' => 1, 'name' => 'one'], true);
        $propertyExample = $example->propertyExample();

        $this->assertInstanceOf(ValidPropertiesTest::class, $example);
        $this->assertNotNull($propertyExample);
        $this->assertSame([1, 'one'], $propertyExample->arguments());
        $this->assertTrue($propertyExample->failureIsReported());
    }

    #[WithoutErrorHandler]
    public function testRunsAnExampleWithItsEventsCollected(): void
    {
        $runner = $this->runner('testWithoutParameters');

        [$events, $run] = $runner->run($runner->example([]), Facade::emitter(), false);

        $this->assertSame(
            [PreparationStarted::class, Prepared::class, Passed::class, Finished::class],
            $this->classesOf($events),
        );
        $this->assertNull($run->contributedToCodeCoverage());
        $this->assertFalse(ExampleRunner::endedBeforeItWasPrepared($events));
    }

    public function testAnExampleWithoutATestPreparedEventEndedBeforeItWasPrepared(): void
    {
        $this->assertTrue(ExampleRunner::endedBeforeItWasPrepared(new EventCollection));
    }

    public function testGeneratesTheArgumentsOfAnExampleFromASourceOfChoices(): void
    {
        $runner = $this->runner('testWithGeneratorMethodWithOptionalParameter');

        $arguments = $runner->generateArguments($this->generators('testWithGeneratorMethodWithOptionalParameter'), ChoiceSource::replay(new ChoiceSequence([])));

        $this->assertSame(['value' => 0], $arguments);
    }

    public function testGeneratesTheArgumentsOfAnExampleWithinATimeLimit(): void
    {
        $runner = $this->runner('testWithGeneratorMethodWithOptionalParameter');

        $arguments = $runner->generateArgumentsWithinTimeLimit($this->generators('testWithGeneratorMethodWithOptionalParameter'), ChoiceSource::replay(new ChoiceSequence([])), 1);

        $this->assertSame(['value' => 0], $arguments);
    }

    public function testExportsTheArgumentsOfAnExampleAsPhpCode(): void
    {
        $exported = $this->runner('testWithExplicitExamples')->export(['small' => 1, 'name' => 'one'], new Constructions);

        $this->assertSame(['small' => '1', 'name' => "'one'"], $exported);
    }

    #[WithoutErrorHandler]
    public function testReturnsWhatTheCallableThatGeneratesValuesReturnsWithTheEventsThatAreEmittedMeanwhileCollected(): void
    {
        [$facade, $tracer] = $this->sealedFacadeWithTracer();

        $events = $this->eventsWith($tracer->events[0]);

        $value = $this->runner('testWithoutParameters', $facade)->handlingIssues(
            static function () use ($facade, $events): string
            {
                $facade->forward($events);

                return 'value';
            },
            null,
        );

        $this->assertSame('value', $value);
        $this->assertCount(1, $tracer->events);

        $facade->forward($events);

        $this->assertCount(2, $tracer->events);
    }

    #[WithoutErrorHandler]
    public function testStopsCollectingTheEventsWhenTheCallableThatGeneratesValuesRaisesAnException(): void
    {
        [$facade, $tracer] = $this->sealedFacadeWithTracer();

        $exception = new LogicException;

        try {
            $this->runner('testWithoutParameters', $facade)->handlingIssues(
                static fn (): never => throw $exception,
                null,
            );

            $this->fail('The exception was not raised');
        } catch (LogicException $e) {
            $this->assertSame($exception, $e);
        }

        $facade->forward($this->eventsWith($tracer->events[0]));

        $this->assertCount(2, $tracer->events);
    }

    public function testDoesNotTriggerTheTestCaseContextIssuesOfAPropertyThatDoesNotUsePhpunitsErrorHandler(): void
    {
        $this->runner('testWithoutParameters')->triggerTestCaseContextIssues();

        $this->assertTrue(true);
    }

    /**
     * @param non-empty-string $methodName
     */
    private function runner(string $methodName, ?Facade $facade = null): ExampleRunner
    {
        $test = new ValidPropertiesTest($methodName);

        if ($facade === null) {
            $facade = Facade::instance();
        }

        return new ExampleRunner(
            $test->valueObjectForEvents(),
            false,
            static fn (): TestCase => new ValidPropertiesTest($methodName),
            new GeneratorResolver(new PropertyDefinitionBuilder()->build(ValidPropertiesTest::class, $methodName)),
            $facade,
            FalsifyingIssues::fromConfiguration(ConfigurationRegistry::get()),
        );
    }

    /**
     * @param non-empty-string $methodName
     *
     * @return array<non-empty-string, Generator\AbstractGenerator<mixed>>
     */
    private function generators(string $methodName): array
    {
        [, $generators] = new GeneratorResolver(new PropertyDefinitionBuilder()->build(ValidPropertiesTest::class, $methodName))->generators(new ValidPropertiesTest($methodName), $this->createStub(Emitter::class));

        return $generators;
    }

    /**
     * @return list<class-string>
     */
    private function classesOf(EventCollection $events): array
    {
        $classes = [];

        foreach ($events as $event) {
            $classes[] = $event::class;
        }

        return $classes;
    }

    private function eventsWith(Event $event): EventCollection
    {
        $events = new EventCollection;

        $events->add($event);

        return $events;
    }

    /**
     * @return array{0: Facade, 1: object{events: list<Event>}&Tracer}
     */
    private function sealedFacadeWithTracer(): array
    {
        $tracer = new class implements Tracer
        {
            /**
             * @var list<Event>
             */
            public array $events = [];

            public function trace(Event $event): void
            {
                $this->events[] = $event;
            }
        };

        $facade = new Facade;

        $facade->registerTracer($tracer);
        $facade->seal();

        return [$facade, $tracer];
    }
}
