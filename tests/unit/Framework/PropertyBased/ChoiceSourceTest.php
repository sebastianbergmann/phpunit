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

use function array_map;
use function array_unique;
use function count;
use function serialize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Distribution\ExactDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(ChoiceSource::class)]
#[CoversClass(ChoiceTree::class)]
#[CoversClass(ChoiceTreeNode::class)]
#[CoversClass(ChoiceSequence::class)]
#[CoversClass(Span::class)]
#[CoversClass(ExampleRejectedException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ChoiceSourceTest extends TestCase
{
    public function testDrawsChoicesAtRandomWithinTheirRange(): void
    {
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)));

        for ($i = 0; $i < 100; $i++) {
            $choice = $source->draw(5, new UniformDistribution);

            $this->assertGreaterThanOrEqual(0, $choice);
            $this->assertLessThanOrEqual(5, $choice);
        }
    }

    public function testRecordsTheChoicesItDraws(): void
    {
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)));

        $choices = [];

        for ($i = 0; $i < 10; $i++) {
            $choices[] = $source->draw(1000, new UniformDistribution);
        }

        $this->assertSame($choices, $source->sequence()->choices());
    }

    public function testDrawsTheSameChoicesForTheSameSeed(): void
    {
        $first  = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(42)));
        $second = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(42)));

        for ($i = 0; $i < 10; $i++) {
            $this->assertSame(
                $first->draw(1000000, new UniformDistribution),
                $second->draw(1000000, new UniformDistribution),
            );
        }
    }

    public function testReplaysTheChoicesOfASequence(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([3, 1, 4]));

        $this->assertSame(3, $source->draw(10, new UniformDistribution));
        $this->assertSame(1, $source->draw(10, new UniformDistribution));
        $this->assertSame(4, $source->draw(10, new UniformDistribution));
        $this->assertSame([3, 1, 4], $source->sequence()->choices());
    }

    public function testReplaysZeroForChoicesBeyondTheEndOfTheSequence(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([3]));

        $this->assertSame(3, $source->draw(10, new UniformDistribution));
        $this->assertSame(0, $source->draw(10, new UniformDistribution));
        $this->assertSame(0, $source->draw(10, new UniformDistribution));
        $this->assertSame([3, 0, 0], $source->sequence()->choices());
    }

    public function testClampsAReplayedChoiceThatExceedsItsRange(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([7]));

        $this->assertSame(2, $source->draw(2, new UniformDistribution));
        $this->assertSame([2], $source->sequence()->choices());
    }

    public function testRejectsTheExampleWhenItRequiresMoreThanTheMaximumNumberOfChoices(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]), 2);

        $source->draw(1, new UniformDistribution);
        $source->draw(1, new UniformDistribution);

        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessageIs('Generating the example required more than 2 choices');

        $source->draw(1, new UniformDistribution);
    }

    public function testRecordsSpansOfTheChoicesThatWereDrawnBetweenTheirStartAndEndInTheOrderInWhichTheyWereStarted(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $source->startSpan('outer');
        $source->draw(1, new UniformDistribution);
        $source->startSpan('inner');
        $source->draw(1, new UniformDistribution);
        $source->draw(1, new UniformDistribution);
        $source->endSpan();
        $source->endSpan();

        $spans = $source->sequence()->spans();

        $this->assertCount(2, $spans);

        $this->assertSame(0, $spans[0]->start());
        $this->assertSame(3, $spans[0]->end());
        $this->assertSame(3, $spans[0]->length());
        $this->assertSame('outer', $spans[0]->label());

        $this->assertSame(1, $spans[1]->start());
        $this->assertSame(3, $spans[1]->end());
        $this->assertSame(2, $spans[1]->length());
        $this->assertSame('inner', $spans[1]->label());
    }

    public function testRecordsTheSpanThatASpanIsPartOf(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $source->startSpan('outer');
        $source->startSpan('first');
        $source->draw(1, new UniformDistribution);
        $source->endSpan();
        $source->startSpan('second');
        $source->draw(1, new UniformDistribution);
        $source->endSpan();
        $source->endSpan();
        $source->startSpan('next');
        $source->draw(1, new UniformDistribution);
        $source->endSpan();

        $spans = $source->sequence()->spans();

        $this->assertSame(['outer', 'first', 'second', 'next'], array_map(static fn (Span $span): string => $span->label(), $spans));
        $this->assertSame([null, 0, 0, null], array_map(static fn (Span $span): ?int => $span->parent(), $spans));
    }

    public function testDoesNotRecordSpansWithoutChoices(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $source->startSpan('outer');
        $source->startSpan('empty');
        $source->endSpan();
        $source->startSpan('inner');
        $source->draw(1, new UniformDistribution);
        $source->endSpan();
        $source->endSpan();

        $spans = $source->sequence()->spans();

        $this->assertSame(['outer', 'inner'], array_map(static fn (Span $span): string => $span->label(), $spans));
        $this->assertSame([null, 0], array_map(static fn (Span $span): ?int => $span->parent(), $spans));
    }

    public function testDoesNotRecordSpansThatWereNotEnded(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $source->startSpan('outer');
        $source->startSpan('ended');
        $source->draw(1, new UniformDistribution);
        $source->endSpan();
        $source->startSpan('not ended');
        $source->draw(1, new UniformDistribution);

        $spans = $source->sequence()->spans();

        $this->assertCount(1, $spans);
        $this->assertSame('ended', $spans[0]->label());
        $this->assertNull($spans[0]->parent());
    }

    public function testChoosesTheElementAtTheDrawnIndex(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([2, 0, 9]));

        $this->assertSame('c', $source->choose(['a', 'b', 'c'], new UniformDistribution));
        $this->assertSame('a', $source->choose(['a', 'b', 'c'], new UniformDistribution));
        $this->assertSame('c', $source->choose(['a', 'b', 'c'], new UniformDistribution));
    }

    public function testSuggestsACandidateWithTheGivenProbabilityWhileChoicesAreDrawnAtRandom(): void
    {
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)));

        $this->assertSame('candidate', $source->suggest(['candidate'], 1.0));
        $this->assertNull($source->suggest(['candidate'], 0.0));
        $this->assertSame([], $source->sequence()->choices());
    }

    public function testProvidesTheDeriverItIsCreatedWith(): void
    {
        $deriver = new GeneratorDeriver(GeneratorRegistry::empty());

        $this->assertSame($deriver, ChoiceSource::replay(new ChoiceSequence([]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver)->deriver());
    }

    public function testProvidesADeriverWithoutRegisteredGeneratorsWhenItIsCreatedWithoutOne(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $this->assertSame($source->deriver(), $source->deriver());
    }

    public function testRecordsHowObjectsWereConstructed(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));
        $object = new ChoiceSequence([]);

        $this->assertSame($source->constructions(), $source->constructions());
        $this->assertFalse($source->constructions()->has($object));

        $source->constructions()->record($object, ChoiceSequence::class, ['choices' => []]);

        $this->assertSame(['className' => ChoiceSequence::class, 'arguments' => ['choices' => []]], $source->constructions()->of($object));
    }

    public function testCountsTheObjectsOfAClassThatAreBeingGenerated(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $this->assertSame(1, $source->startObject(self::class));
        $this->assertSame(2, $source->startObject(self::class));
        $this->assertSame(1, $source->startObject(TestCase::class));

        $source->endObject(self::class);

        $this->assertSame(2, $source->startObject(self::class));

        $source->endObject(self::class);
        $source->endObject(self::class);

        $this->assertSame(1, $source->startObject(self::class));
    }

    public function testDoesNotSuggestACandidateWhileASequenceIsReplayed(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $this->assertNull($source->suggest(['candidate'], 1.0));
    }

    public function testDoesNotDrawTheChoicesOfAnExampleThatWasGeneratedBefore(): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $tree       = new ChoiceTree;
        $sequences  = [];

        for ($i = 0; $i < 10; $i++) {
            $source = ChoiceSource::random($randomizer, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

            $source->draw(1, new UniformDistribution);
            $source->draw(4, new UniformDistribution);
            $source->exampleGenerated();

            $sequences[] = $source->sequence()->choices();
        }

        $this->assertSame(10, count(array_unique(array_map(serialize(...), $sequences))));
        $this->assertTrue($tree->isExhausted());
    }

    public function testRecordsTheChoicesOfAReplayedSequenceInTheChoiceTree(): void
    {
        $tree   = new ChoiceTree;
        $source = ChoiceSource::replay(new ChoiceSequence([0]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->draw(1, new UniformDistribution);
        $source->exampleGenerated();

        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $this->assertSame(1, $source->draw(1, new ExactDistribution(0)));
    }

    public function testNoLongerUsesTheChoiceTreeWhenAChoiceIsDrawnFromADifferentRangeThanBefore(): void
    {
        $tree   = new ChoiceTree;
        $source = ChoiceSource::replay(new ChoiceSequence([0]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->draw(1, new UniformDistribution);
        $source->exampleGenerated();

        $source = ChoiceSource::replay(new ChoiceSequence([0]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->draw(2, new UniformDistribution);

        $this->assertNull($tree->root());
    }

    public function testKnowsWhetherAChoiceWasDrawnFromADifferentRangeThanBefore(): void
    {
        $tree   = new ChoiceTree;
        $source = ChoiceSource::replay(new ChoiceSequence([0]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->draw(1, new UniformDistribution);
        $source->exampleGenerated();

        $this->assertFalse($source->drewDifferentlyThanBefore());

        $source = ChoiceSource::replay(new ChoiceSequence([0]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->draw(2, new UniformDistribution);

        $this->assertTrue($source->drewDifferentlyThanBefore());
    }

    public function testDrawsChoicesOfAnExampleThatWasGeneratedBeforeWhenEveryExampleWasGenerated(): void
    {
        $tree   = new ChoiceTree;
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->draw(0, new UniformDistribution);
        $source->exampleGenerated();

        $this->assertTrue($tree->isExhausted());

        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $this->assertSame(0, $source->draw(0, new UniformDistribution));
    }
}
