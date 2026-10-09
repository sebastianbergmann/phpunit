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
use function array_fill;
use function array_slice;
use function array_splice;
use function array_values;
use function assert;
use function count;
use function hash;
use function implode;
use function intdiv;
use function min;
use function usort;
use Closure;

/**
 * Shrinks the choice sequence of a counterexample: it repeatedly tries
 * simpler choice sequences and keeps one when replaying it falsifies the
 * property in the same way.
 *
 * The shrinker works on choice sequences only. It neither knows the
 * generators that build values from the choices nor how a candidate is
 * checked, so composed generators shrink as well as the ones they are
 * composed of.
 *
 * Its passes are deleting spans, deleting blocks of consecutive choices,
 * setting blocks of choices to 0, minimizing individual choices by trying
 * small values and then searching for the smallest value with a binary
 * search, redistributing pairs of nearby choices by moving as much as
 * possible from the first choice to the second, and lowering equal choices
 * together. The passes are repeated until none of them succeeds, the
 * maximum number of attempts has been made, or shrinking is interrupted
 * because the test run is to be stopped.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Shrinker
{
    private const array BLOCK_SIZES                   = [8, 4, 2, 1];
    private const int LARGEST_SMALL_VALUE             = 3;
    private const int LARGEST_REDISTRIBUTION_DISTANCE = 4;

    /**
     * @var Closure(ChoiceSequence): ?ChoiceSequence
     */
    private readonly Closure $test;

    /**
     * @var non-negative-int
     */
    private readonly int $maximumNumberOfAttempts;

    /**
     * @var ?Closure(): bool
     */
    private readonly ?Closure $shouldStop;
    private ChoiceSequence $current;

    /**
     * @var non-negative-int
     */
    private int $numberOfAttempts = 0;

    /**
     * @var non-negative-int
     */
    private int $numberOfSteps    = 0;
    private bool $budgetExhausted = false;
    private bool $interrupted     = false;

    /**
     * The candidates that have been tried, by a hash of their choices, so
     * that the memory this needs does not grow with the number of choices.
     *
     * @var array<string, true>
     */
    private array $tried = [];

    /**
     * @param Closure(ChoiceSequence): ?ChoiceSequence $test                    replays a candidate and returns the choice
     *                                                                          sequence that the replay consumed when the
     *                                                                          candidate falsifies the property in the
     *                                                                          same way, and null otherwise
     * @param non-negative-int                         $maximumNumberOfAttempts
     * @param ?Closure(): bool                         $shouldStop              whether shrinking is to be interrupted, which is asked before each attempt
     */
    public function __construct(Closure $test, int $maximumNumberOfAttempts, ?Closure $shouldStop = null)
    {
        $this->test                    = $test;
        $this->maximumNumberOfAttempts = $maximumNumberOfAttempts;
        $this->shouldStop              = $shouldStop;
        $this->current                 = new ChoiceSequence([]);
    }

    public function shrink(ChoiceSequence $sequence): ShrinkingResult
    {
        $this->current          = $sequence;
        $this->numberOfAttempts = 0;
        $this->numberOfSteps    = 0;
        $this->budgetExhausted  = false;
        $this->interrupted      = false;
        $this->tried            = [];

        // shrinking is turned off when no attempt may be made, which does not
        // exhaust the budget for shrinking
        if ($this->maximumNumberOfAttempts === 0) {
            return new ShrinkingResult($sequence, 0, 0, false, false);
        }

        do {
            $numberOfStepsBefore = $this->numberOfSteps;

            $this->deleteSpans();
            $this->deleteBlocks();
            $this->zeroBlocks();
            $this->minimizeChoices();
            $this->redistributeChoices();
            $this->lowerEqualChoices();
        } while ($this->numberOfSteps > $numberOfStepsBefore && !$this->isStopped());

        return new ShrinkingResult(
            $this->current,
            $this->numberOfAttempts,
            $this->numberOfSteps,
            $this->budgetExhausted,
            $this->interrupted,
        );
    }

    /**
     * Deleting the span of an element of a collection deletes the element.
     * Longer spans are tried first.
     */
    private function deleteSpans(): void
    {
        $index = 0;

        while (!$this->isStopped()) {
            $spans = $this->spansOfCurrentSequence();

            if ($index >= count($spans)) {
                return;
            }

            // after a successful deletion, the span at the same index of the
            // simpler sequence is tried next
            if (!$this->consider($this->withoutChoices($spans[$index]->start(), $spans[$index]->length()))) {
                $index++;
            }
        }
    }

    private function deleteBlocks(): void
    {
        foreach (self::BLOCK_SIZES as $size) {
            $start = $this->length() - $size;

            while ($start >= 0 && !$this->isStopped()) {
                if ($this->consider($this->withoutChoices($start, $size))) {
                    $start = min($start, $this->length() - $size);

                    continue;
                }

                $start--;
            }
        }
    }

    private function zeroBlocks(): void
    {
        foreach (self::BLOCK_SIZES as $size) {
            for ($start = 0; $start + $size <= $this->length() && !$this->isStopped(); $start++) {
                if ($this->isZero($start, $size)) {
                    continue;
                }

                $this->consider($this->withZeroedChoices($start, $size));
            }
        }
    }

    private function minimizeChoices(): void
    {
        for ($index = 0; $index < $this->length() && !$this->isStopped(); $index++) {
            $this->minimizeChoice($index);
        }
    }

    /**
     * @param non-negative-int $index
     */
    private function minimizeChoice(int $index): void
    {
        $choice = $this->choiceAt($index);

        for ($value = 0; $value <= self::LARGEST_SMALL_VALUE && $value < $choice; $value++) {
            if ($this->consider($this->withChoice($index, $value))) {
                return;
            }
        }

        // the largest small value that was tried is a lower bound that does
        // not falsify the property, and the current choice is an upper bound
        // that does
        $lower = self::LARGEST_SMALL_VALUE;
        $upper = $choice;

        while ($upper - $lower > 1 && !$this->isStopped()) {
            $middle = $lower + intdiv($upper - $lower, 2);

            assert($middle >= 0);

            if (!$this->consider($this->withChoice($index, $middle))) {
                $lower = $middle;

                continue;
            }

            if ($index >= $this->length()) {
                return;
            }

            $upper = $this->choiceAt($index);
        }
    }

    /**
     * A counterexample for a property such as $a + $b > 10 cannot be shrunk
     * from [11, 0] to [0, 11] by minimizing one choice at a time. Moving an
     * amount from a choice to a later one keeps their sum, while the choice
     * sequence becomes simpler.
     */
    private function redistributeChoices(): void
    {
        for ($first = 0; $first < $this->length() && !$this->isStopped(); $first++) {
            for ($second = $first + 1; $second <= $first + self::LARGEST_REDISTRIBUTION_DISTANCE && $second < $this->length() && !$this->isStopped(); $second++) {
                $this->redistribute($first, $second);
            }
        }
    }

    /**
     * Moves as much as possible from the choice at $first to the choice at
     * $second: everything if that keeps the property falsified, otherwise the
     * largest amount that a binary search finds.
     *
     * @param non-negative-int $first
     * @param non-negative-int $second
     */
    private function redistribute(int $first, int $second): void
    {
        $firstChoice  = $this->choiceAt($first);
        $secondChoice = $this->choiceAt($second);

        // the amount that can be moved without exceeding the largest choice
        $movable = min($firstChoice, PHP_INT_MAX - $secondChoice);

        if ($movable < 1) {
            return;
        }

        if ($this->consider($this->withMovedAmount($first, $second, $movable))) {
            return;
        }

        // moving nothing keeps the property falsified, moving everything
        // that can be moved does not
        $lower = 0;
        $upper = $movable;

        while ($upper - $lower > 1 && !$this->isStopped()) {
            $middle = $lower + intdiv($upper - $lower, 2);

            assert($middle > 0);

            $candidate = $this->withMovedAmount($first, $second, $middle, $firstChoice, $secondChoice);

            if (!$this->consider($candidate)) {
                $upper = $middle;

                continue;
            }

            // the amounts are relative to the choices from which the pair is
            // redistributed, which the consumed choice sequence may not have
            if ($this->current->choices() !== $candidate) {
                return;
            }

            $lower = $middle;
        }
    }

    /**
     * A counterexample for a property such as "a list has no duplicates"
     * cannot be shrunk from [-9, -9] to [0, 0] by minimizing one choice at a
     * time, because lowering a choice of one of two equal values makes them
     * unequal. Equal values are generated from spans of equal choices, and
     * the choices at the same position in these spans are lowered together,
     * which keeps the values equal while the choice sequence becomes simpler.
     */
    private function lowerEqualChoices(): void
    {
        $choices        = $this->current->choices();
        $spansByChoices = [];

        foreach ($this->spansOfCurrentSequence() as $span) {
            $spansByChoices[implode(',', array_slice($choices, $span->start(), $span->length()))][] = $span;
        }

        foreach ($spansByChoices as $spans) {
            if (count($spans) < 2) {
                continue;
            }

            for ($offset = 0; $offset < $spans[0]->length() && !$this->isStopped(); $offset++) {
                $indexes = [];

                foreach ($spans as $span) {
                    $indexes[] = $span->start() + $offset;
                }

                $this->lowerTogether($indexes);
            }
        }
    }

    /**
     * Lowers the choices at the indexes, which have to be equal, to the
     * smallest value that keeps the property falsified: a small value if
     * possible, otherwise the value that a binary search finds.
     *
     * @param non-empty-list<non-negative-int> $indexes
     */
    private function lowerTogether(array $indexes): void
    {
        // an earlier step may have changed the choices at the indexes
        $choice = $this->equalChoiceAt($indexes);

        if ($choice === null || $choice === 0) {
            return;
        }

        for ($value = 0; $value <= self::LARGEST_SMALL_VALUE && $value < $choice; $value++) {
            if ($this->consider($this->withChoices($indexes, $value))) {
                return;
            }
        }

        // the largest small value that was tried is a lower bound that does
        // not falsify the property, and the current choice is an upper bound
        // that does
        $lower = self::LARGEST_SMALL_VALUE;
        $upper = $choice;

        while ($upper - $lower > 1 && !$this->isStopped()) {
            $middle = $lower + intdiv($upper - $lower, 2);

            assert($middle >= 0);

            $candidate = $this->withChoices($indexes, $middle);

            if (!$this->consider($candidate)) {
                $lower = $middle;

                continue;
            }

            // the bounds refer to the choices at the indexes, which the
            // consumed choice sequence may not have
            if ($this->current->choices() !== $candidate) {
                return;
            }

            $upper = $middle;
        }
    }

    /**
     * Returns the value of the choices at the indexes when the current choice
     * sequence has all of them and they are equal, and null otherwise.
     *
     * @param non-empty-list<non-negative-int> $indexes
     *
     * @return ?non-negative-int
     */
    private function equalChoiceAt(array $indexes): ?int
    {
        $choices = $this->current->choices();
        $choice  = null;

        foreach ($indexes as $index) {
            if (!isset($choices[$index]) || ($choice !== null && $choices[$index] !== $choice)) {
                return null;
            }

            $choice = $choices[$index];
        }

        return $choice;
    }

    /**
     * @param non-empty-list<non-negative-int> $indexes
     * @param non-negative-int                 $value
     *
     * @return list<non-negative-int>
     */
    private function withChoices(array $indexes, int $value): array
    {
        $choices = $this->current->choices();

        foreach ($indexes as $index) {
            array_splice($choices, $index, 1, [$value]);
        }

        return $choices;
    }

    /**
     * Replays the candidate, unless it has been tried before, the maximum
     * number of attempts has been reached, or shrinking is interrupted, and
     * keeps the choice sequence that the replay consumed when the candidate
     * falsifies the property in the same way and that sequence is simpler
     * than the current one.
     *
     * @param list<non-negative-int> $choices
     */
    private function consider(array $choices): bool
    {
        $key = hash('xxh128', implode(',', $choices));

        if (isset($this->tried[$key])) {
            return false;
        }

        if ($this->numberOfAttempts >= $this->maximumNumberOfAttempts) {
            $this->budgetExhausted = true;

            return false;
        }

        if ($this->shouldStop !== null && ($this->shouldStop)()) {
            $this->interrupted = true;

            return false;
        }

        $this->tried[$key] = true;

        $this->numberOfAttempts++;

        $consumed = ($this->test)(new ChoiceSequence($choices));

        if ($consumed === null || !$consumed->isSimplerThan($this->current)) {
            return false;
        }

        $this->current = $consumed;

        $this->numberOfSteps++;

        return true;
    }

    /**
     * Whether no more candidates are replayed, because the maximum number of
     * attempts has been made or shrinking was interrupted.
     */
    private function isStopped(): bool
    {
        return $this->budgetExhausted || $this->interrupted;
    }

    /**
     * @return list<Span> the distinct spans of the current sequence, longest first
     */
    private function spansOfCurrentSequence(): array
    {
        $spans = [];

        foreach ($this->current->spans() as $span) {
            $spans[$span->start() . ':' . $span->end()] = $span;
        }

        $spans = array_values($spans);

        usort(
            $spans,
            static function (Span $a, Span $b): int
            {
                if ($a->length() !== $b->length()) {
                    return $b->length() <=> $a->length();
                }

                return $a->start() <=> $b->start();
            },
        );

        return $spans;
    }

    /**
     * @param non-negative-int $index
     *
     * @return non-negative-int
     */
    private function choiceAt(int $index): int
    {
        $choices = $this->current->choices();

        assert(isset($choices[$index]));

        return $choices[$index];
    }

    /**
     * @return non-negative-int
     */
    private function length(): int
    {
        return count($this->current->choices());
    }

    /**
     * @param non-negative-int $start
     * @param positive-int     $size
     */
    private function isZero(int $start, int $size): bool
    {
        foreach (array_slice($this->current->choices(), $start, $size) as $choice) {
            if ($choice !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param non-negative-int $start
     * @param positive-int     $size
     *
     * @return list<non-negative-int>
     */
    private function withoutChoices(int $start, int $size): array
    {
        $choices = $this->current->choices();

        array_splice($choices, $start, $size);

        return $choices;
    }

    /**
     * @param non-negative-int $start
     * @param positive-int     $size
     *
     * @return list<non-negative-int>
     */
    private function withZeroedChoices(int $start, int $size): array
    {
        $choices = $this->current->choices();

        array_splice($choices, $start, $size, array_fill(0, $size, 0));

        return $choices;
    }

    /**
     * @param non-negative-int  $first
     * @param non-negative-int  $second
     * @param positive-int      $amount
     * @param ?non-negative-int $firstChoice  the choice at $first to move the amount from, the current one when null
     * @param ?non-negative-int $secondChoice the choice at $second to move the amount to, the current one when null
     *
     * @return list<non-negative-int>
     */
    private function withMovedAmount(int $first, int $second, int $amount, ?int $firstChoice = null, ?int $secondChoice = null): array
    {
        if ($firstChoice === null) {
            $firstChoice = $this->choiceAt($first);
        }

        if ($secondChoice === null) {
            $secondChoice = $this->choiceAt($second);
        }

        $firstValue  = $firstChoice - $amount;
        $secondValue = $secondChoice + $amount;

        assert($firstValue >= 0 && $secondValue >= 0);

        $choices = $this->current->choices();

        array_splice($choices, $first, 1, [$firstValue]);
        array_splice($choices, $second, 1, [$secondValue]);

        return $choices;
    }

    /**
     * @param non-negative-int $index
     * @param non-negative-int $value
     *
     * @return list<non-negative-int>
     */
    private function withChoice(int $index, int $value): array
    {
        $choices = $this->current->choices();

        array_splice($choices, $index, 1, [$value]);

        return $choices;
    }
}
