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

use function array_key_exists;
use function array_shift;
use function array_slice;
use function count;
use function max;
use function range;
use Random\Randomizer;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

/**
 * Searches for examples with higher scores than the examples that were
 * checked so far, for the scores that the examples of a property target with
 * TestCase::target().
 *
 * For each label, the search climbs from the choice sequence of the example
 * with the highest score: it changes one choice of the sequence at a time,
 * first to larger and then to smaller choices, and doubles the change for as
 * long as the score increases. The choices are changed in a random order.
 * Once no change to any of the choices increased the score, the example is
 * a local maximum, and the search for the label ends, until an example with
 * an even higher score is checked. The climbs of the labels take turns.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TargetedSearch
{
    private Randomizer $randomizer;

    /**
     * For each label, in the order in which the labels were first used, the
     * highest score and the choice sequence of the example that reached it,
     * and the climb from that example: the indexes of the choices that are
     * still to be changed in the current pass over its choices (null before
     * the first pass), the index of the choice that is changed, the
     * direction and the size of the change, and whether a change increased
     * the score in the current pass.
     *
     * @var array<non-empty-string, array{score: float|int, sequence: ChoiceSequence, indexes: ?list<non-negative-int>, index: ?non-negative-int, direction: -1|1, change: positive-int, increased: bool}>
     */
    private array $labels = [];

    /**
     * The label whose climb the candidate that is checked belongs to.
     *
     * @var ?non-empty-string
     */
    private ?string $candidateFor = null;

    /**
     * The number of candidates that were returned, which determines whose
     * climb the next candidate belongs to.
     *
     * @var non-negative-int
     */
    private int $numberOfCandidates = 0;

    public function __construct(Randomizer $randomizer)
    {
        $this->randomizer = $randomizer;
    }

    /**
     * The choice sequence of the next example that is checked to find a
     * higher score, null when no score was targeted or every climb has
     * reached a local maximum.
     */
    public function nextCandidate(): ?ChoiceSequence
    {
        $climbs = [];

        foreach ($this->labels as $label => $state) {
            $climbs[] = ['label' => $label, 'state' => $state];
        }

        $first = 0;

        if ($climbs !== []) {
            $first = $this->numberOfCandidates % count($climbs);
        }

        foreach ([...array_slice($climbs, $first), ...array_slice($climbs, 0, $first)] as $climb) {
            [$candidate, $state] = $this->candidate($climb['state']);

            $this->labels[$climb['label']] = $state;

            if ($candidate !== null) {
                $this->numberOfCandidates++;
                $this->candidateFor = $climb['label'];

                return $candidate;
            }
        }

        return null;
    }

    /**
     * Records the scores of an example that was checked, which is either the
     * candidate that nextCandidate() returned last or another example.
     *
     * @param array<non-empty-string, float|int> $scores
     */
    public function exampleChecked(ChoiceSequence $sequence, array $scores): void
    {
        $labels                     = $this->labels;
        $candidateIncreasedItsScore = false;

        foreach ($scores as $label => $score) {
            if (!array_key_exists($label, $labels)) {
                $labels[$label] = ['score' => $score, 'sequence' => $sequence, 'indexes' => null, 'index' => null, 'direction' => 1, 'change' => 1, 'increased' => false];

                continue;
            }

            if ($score <= $labels[$label]['score']) {
                continue;
            }

            if ($label === $this->candidateFor) {
                // the climb goes on in the same direction with a larger change
                $labels[$label]['score']     = $score;
                $labels[$label]['sequence']  = $sequence;
                $labels[$label]['change']    = 2 * $labels[$label]['change'];
                $labels[$label]['increased'] = true;

                $candidateIncreasedItsScore = true;

                continue;
            }

            // the climb starts over from the example with the higher score
            $labels[$label] = ['score' => $score, 'sequence' => $sequence, 'indexes' => null, 'index' => null, 'direction' => 1, 'change' => 1, 'increased' => false];
        }

        $this->labels = $labels;

        if (!$candidateIncreasedItsScore) {
            $this->candidateDidNotIncreaseItsScore();
        }

        $this->candidateFor = null;
    }

    /**
     * Records that an example was rejected, which does not increase the
     * score when it is a candidate.
     */
    public function exampleRejected(): void
    {
        $this->candidateDidNotIncreaseItsScore();

        $this->candidateFor = null;
    }

    /**
     * The highest score of each label, in the order in which the labels
     * were first used.
     *
     * @return list<array{label: non-empty-string, highestScore: float|int}>
     */
    public function highestScores(): array
    {
        $highestScores = [];

        foreach ($this->labels as $label => $state) {
            $highestScores[] = ['label' => $label, 'highestScore' => $state['score']];
        }

        return $highestScores;
    }

    private function candidateDidNotIncreaseItsScore(): void
    {
        $labels = $this->labels;

        if ($this->candidateFor === null || !array_key_exists($this->candidateFor, $labels)) {
            return;
        }

        $this->labels[$this->candidateFor] = self::changeNextChoice($labels[$this->candidateFor]);
    }

    /**
     * The next candidate of a climb, null when the climb has reached a local
     * maximum, and the state of the climb after it.
     *
     * @param array{score: float|int, sequence: ChoiceSequence, indexes: ?list<non-negative-int>, index: ?non-negative-int, direction: -1|1, change: positive-int, increased: bool} $state
     *
     * @return array{0: ?ChoiceSequence, 1: array{score: float|int, sequence: ChoiceSequence, indexes: ?list<non-negative-int>, index: ?non-negative-int, direction: -1|1, change: positive-int, increased: bool}}
     */
    private function candidate(array $state): array
    {
        $choices = $state['sequence']->choices();

        while (true) {
            $index = $state['index'];

            if ($index === null) {
                if ($state['indexes'] === [] && !$state['increased']) {
                    return [null, $state];
                }

                // a pass over all choices starts when the climb starts, and
                // again when a change increased the score in the last pass
                if ($state['indexes'] === null || $state['indexes'] === []) {
                    $state['indexes']   = $this->shuffledIndexes(count($choices));
                    $state['increased'] = false;
                }

                $index = array_shift($state['indexes']);

                if ($index === null) {
                    return [null, $state];
                }

                $state['index']     = $index;
                $state['direction'] = 1;
                $state['change']    = 1;
            }

            // the choice is absent when the example with the highest score
            // drew fewer choices than the one from which its climb started
            if (!array_key_exists($index, $choices)) {
                $state = self::changeNextChoice($state);

                continue;
            }

            $choice = max(0, $choices[$index] + $state['direction'] * $state['change']);

            if ($choice === $choices[$index]) {
                $state = self::changeNextChoice($state);

                continue;
            }

            $choices[$index] = $choice;

            return [new ChoiceSequence($choices), $state];
        }
    }

    /**
     * @param non-negative-int $numberOfChoices
     *
     * @return list<non-negative-int>
     */
    private function shuffledIndexes(int $numberOfChoices): array
    {
        if ($numberOfChoices === 0) {
            return [];
        }

        return $this->randomizer->shuffleArray(range(0, $numberOfChoices - 1));
    }

    /**
     * Changes the choice in the other direction once it was changed to
     * larger choices, and the next choice once it was changed in both
     * directions.
     *
     * @param array{score: float|int, sequence: ChoiceSequence, indexes: ?list<non-negative-int>, index: ?non-negative-int, direction: -1|1, change: positive-int, increased: bool} $state
     *
     * @return array{score: float|int, sequence: ChoiceSequence, indexes: ?list<non-negative-int>, index: ?non-negative-int, direction: -1|1, change: positive-int, increased: bool}
     */
    private static function changeNextChoice(array $state): array
    {
        if ($state['direction'] === 1) {
            $state['direction'] = -1;
            $state['change']    = 1;

            return $state;
        }

        $state['index'] = null;

        return $state;
    }
}
