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

use function sort;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(TargetedSearch::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class TargetedSearchTest extends TestCase
{
    public function testHasNoCandidateWhenNoScoreWasTargeted(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), []);

        $this->assertNull($search->nextCandidate());
        $this->assertSame([], $search->highestScores());
    }

    public function testChangesAChoiceOfTheExampleWithTheHighestScoreToALargerChoiceFirst(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 1]);
        $search->exampleChecked(new ChoiceSequence([3]), ['score' => 0]);

        $this->assertSame([6], $this->choices($search->nextCandidate()));
    }

    public function testDoublesTheChangeForAsLongAsTheScoreIncreases(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);

        $this->assertSame([6], $this->choices($search->nextCandidate()));

        $search->exampleChecked(new ChoiceSequence([6]), ['score' => 6]);

        $this->assertSame([8], $this->choices($search->nextCandidate()));

        $search->exampleChecked(new ChoiceSequence([8]), ['score' => 8]);

        $this->assertSame([12], $this->choices($search->nextCandidate()));
        $this->assertSame([['label' => 'score', 'highestScore' => 8]], $search->highestScores());
    }

    public function testChangesTheChoiceToSmallerChoicesWhenALargerChoiceDidNotIncreaseTheScore(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([6]), ['score' => 4]);

        $this->assertSame([4], $this->choices($search->nextCandidate()));

        $search->exampleChecked(new ChoiceSequence([4]), ['score' => 6]);

        $this->assertSame([2], $this->choices($search->nextCandidate()));
    }

    public function testEndsTheSearchAtALocalMaximum(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([6]), ['score' => 4]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([4]), ['score' => 4]);

        $this->assertNull($search->nextCandidate());
    }

    public function testStartsAnotherPassOverTheChoicesWhenAChangeIncreasedTheScore(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([6]), ['score' => 6]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([8]), ['score' => 0]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 0]);

        $this->assertSame([7], $this->choices($search->nextCandidate()));
    }

    public function testDoesNotChangeAChoiceToAChoiceSmallerThanZero(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([0]), ['score' => 0]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([1]), ['score' => -1]);

        $this->assertNull($search->nextCandidate());
    }

    public function testARejectedCandidateDoesNotIncreaseTheScore(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);
        $search->nextCandidate();
        $search->exampleRejected();

        $this->assertSame([4], $this->choices($search->nextCandidate()));
    }

    public function testRecordingARejectedExampleThatIsNotACandidateDoesNotChangeTheSearch(): void
    {
        $search = $this->search();

        $search->exampleRejected();
        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);
        $search->exampleRejected();

        $this->assertSame([6], $this->choices($search->nextCandidate()));
    }

    public function testChangesEachChoiceOfTheExampleWithTheHighestScore(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5, 7]), ['score' => 0]);

        $candidates = [];

        while (($candidate = $search->nextCandidate()) !== null) {
            $candidates[] = $candidate->choices();

            $search->exampleChecked($candidate, ['score' => -1]);
        }

        sort($candidates);

        $this->assertSame([[4, 7], [5, 6], [5, 8], [6, 7]], $candidates);
    }

    public function testHasNoCandidateForAnExampleWithoutChoices(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([]), ['score' => 0]);

        $this->assertNull($search->nextCandidate());
    }

    public function testSkipsAChoiceThatTheExampleWithTheHighestScoreDidNotDraw(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 0]);
        $search->nextCandidate();

        // the candidate drew fewer choices than the example it was made from
        $search->exampleChecked(new ChoiceSequence([]), ['score' => 1]);

        $this->assertNull($search->nextCandidate());
    }

    public function testStartsTheClimbOverWhenAnotherExampleReachesAHigherScore(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['score' => 5]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([6]), ['score' => 4]);
        $search->exampleChecked(new ChoiceSequence([20]), ['score' => 20]);

        $this->assertSame([21], $this->choices($search->nextCandidate()));
    }

    public function testTheClimbsOfTheLabelsTakeTurns(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([5]), ['first' => 0, 'second' => 0]);
        $search->exampleChecked(new ChoiceSequence([9]), ['second' => 1]);

        $this->assertSame([6], $this->choices($search->nextCandidate()));

        $search->exampleChecked(new ChoiceSequence([6]), ['first' => -1, 'second' => -1]);

        $this->assertSame([10], $this->choices($search->nextCandidate()));

        $search->exampleChecked(new ChoiceSequence([10]), ['first' => -1, 'second' => -1]);

        $this->assertSame([4], $this->choices($search->nextCandidate()));
        $this->assertSame(
            [
                ['label' => 'first', 'highestScore' => 0],
                ['label' => 'second', 'highestScore' => 1],
            ],
            $search->highestScores(),
        );
    }

    public function testGoesOnWithTheClimbOfAnotherLabelWhenTheClimbOfOneLabelEnded(): void
    {
        $search = $this->search();

        $search->exampleChecked(new ChoiceSequence([0]), ['first' => 0]);
        $search->exampleChecked(new ChoiceSequence([3]), ['second' => 0]);
        $search->nextCandidate();
        $search->exampleChecked(new ChoiceSequence([1]), ['first' => -1]);

        $this->assertSame([4], $this->choices($search->nextCandidate()));

        $search->exampleChecked(new ChoiceSequence([4]), ['second' => -1]);

        $this->assertSame([2], $this->choices($search->nextCandidate()));
    }

    private function search(): TargetedSearch
    {
        return new TargetedSearch(new Randomizer(new Xoshiro256StarStar(1)));
    }

    /**
     * @return list<non-negative-int>
     */
    private function choices(?ChoiceSequence $sequence): array
    {
        $this->assertNotNull($sequence);

        return $sequence->choices();
    }
}
