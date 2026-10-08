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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(ChoiceSource::class)]
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
        $this->expectExceptionMessage('Generating the example required more than 2 choices');

        $source->draw(1, new UniformDistribution);
    }

    public function testRecordsSpansOfTheChoicesThatWereDrawnBetweenTheirStartAndEnd(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $source->startSpan();
        $source->draw(1, new UniformDistribution);
        $source->startSpan();
        $source->draw(1, new UniformDistribution);
        $source->draw(1, new UniformDistribution);
        $source->endSpan();
        $source->endSpan();

        $spans = $source->sequence()->spans();

        $this->assertCount(2, $spans);

        $this->assertSame(1, $spans[0]->start());
        $this->assertSame(3, $spans[0]->end());
        $this->assertSame(2, $spans[0]->length());

        $this->assertSame(0, $spans[1]->start());
        $this->assertSame(3, $spans[1]->end());
        $this->assertSame(3, $spans[1]->length());
    }

    public function testDoesNotRecordSpansWithoutChoices(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $source->startSpan();
        $source->endSpan();

        $this->assertSame([], $source->sequence()->spans());
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

    public function testDoesNotSuggestACandidateWhileASequenceIsReplayed(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $this->assertNull($source->suggest(['candidate'], 1.0));
    }
}
