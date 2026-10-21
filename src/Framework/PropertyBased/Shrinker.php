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
use function array_map;
use function array_reverse;
use function array_slice;
use function array_splice;
use function array_sum;
use function array_values;
use function assert;
use function count;
use function end;
use function hash;
use function implode;
use function intdiv;
use function max;
use function min;
use function sort;
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
 * Its passes are deleting spans, replacing spans with shorter spans they
 * contain that have the same label, moving the values of spans that consist
 * of one choice into the later values of spans with the same label as far
 * as these can take them, deleting blocks of consecutive choices,
 * setting blocks of choices to 0, minimizing individual choices by trying
 * small values and then searching for the smallest value with a binary
 * search, redistributing pairs of nearby choices by moving as much as
 * possible from the first choice to the second, lowering a choice while
 * raising a later one to its largest value, lowering a choice while deleting
 * as many spans of a group of spans that are part of the same span and have
 * the same label after it, lowering equal choices together, and sorting the
 * spans that are part of the same span and have the same label. The passes are repeated until none of them succeeds, the
 * maximum number of attempts has been made, or shrinking is interrupted
 * because the test run is to be stopped. Each round of passes starts with
 * the passes that made the counterexample simpler most often in the round
 * before, so that the attempts are spent on the passes that are most likely
 * to succeed.
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
    private const int LARGEST_RAISING_DISTANCE        = 3;

    /**
     * How many of the groups of spans that are part of the same span, have the
     * same label, and follow a choice, the nearest first, are considered for
     * deleting spans while the choice is lowered.
     */
    private const int LARGEST_NUMBER_OF_GROUPS_AFTER_A_CHOICE = 2;

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

        $passes = [];

        foreach ([
            $this->deleteSpans(...),
            $this->replaceSpansWithDescendants(...),
            $this->moveValuesToLaterValuesWithTheSameLabel(...),
            $this->deleteBlocks(...),
            $this->zeroBlocks(...),
            $this->minimizeChoices(...),
            $this->redistributeChoices(...),
            $this->lowerChoicesRaisingLaterOnes(...),
            $this->lowerChoicesDeletingLaterSpans(...),
            $this->lowerEqualChoices(...),
            $this->sortSiblingSpans(...),
        ] as $pass) {
            $passes[] = ['pass' => $pass, 'numberOfSteps' => 0];
        }

        do {
            $numberOfSteps = 0;

            foreach ($passes as $index => $pass) {
                $numberOfStepsOfPass = $this->numberOfStepsOf($pass['pass']);

                $passes[$index]['numberOfSteps'] = $numberOfStepsOfPass;

                $numberOfSteps += $numberOfStepsOfPass;
            }

            // the sort is stable, so passes that made the counterexample
            // simpler equally often keep their order
            usort(
                $passes,
                static fn (array $a, array $b): int => $b['numberOfSteps'] <=> $a['numberOfSteps'],
            );
        } while ($numberOfSteps > 0 && !$this->isStopped());

        return new ShrinkingResult(
            $this->current,
            $this->numberOfAttempts,
            $this->numberOfSteps,
            $this->budgetExhausted,
            $this->interrupted,
        );
    }

    /**
     * Runs a pass and returns how often it made the counterexample simpler.
     *
     * @param Closure(): void $pass
     *
     * @phpstan-impure
     */
    private function numberOfStepsOf(Closure $pass): int
    {
        $numberOfStepsBefore = $this->numberOfSteps;

        $pass();

        return $this->numberOfSteps - $numberOfStepsBefore;
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
            if (!$this->consider($this->withoutChoices($spans[$index]->start(), $spans[$index]->length())) &&
                !$this->deleteSpanRaisingLaterChoice($spans[$index])) {
                $index++;
            }
        }
    }

    /**
     * What a span contributed to the counterexample can be needed to keep the
     * property falsified, the length of a domain name to which a label
     * contributes, for instance. The span is deleted while its contribution
     * is moved to a later group of spans of a kind that it contains, the
     * top-level domain, for instance, by raising the choice that determines
     * how many spans the group has by the number of choices of the span.
     * Other passes lower the raised choice again, as far as the property
     * stays falsified.
     */
    private function deleteSpanRaisingLaterChoice(Span $span): bool
    {
        $labels = [];

        foreach ($this->current->spans() as $other) {
            if ($other->start() >= $span->start() && $other->end() <= $span->end() && $other->length() < $span->length()) {
                $labels[$other->label()] = $other->label();
            }
        }

        foreach ($labels as $label) {
            foreach ($this->choicesThatDetermineGroupsAfter($span->end(), $label, $this->groupsOfSpans()) as $later) {
                $raised = $later - $span->length();

                // the deleted span precedes the raised choice
                assert($raised >= 0);

                if ($this->consider($this->withRaisedChoice($this->withoutChoices($span->start(), $span->length()), $raised, $later, $span->length()))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A recursive value, a tree for instance, contains values that were
     * generated the same way, its subtrees, whose spans have the same label as
     * the span of the value. Replacing the span of the value with the span of
     * one of these values shrinks it by many levels at once. Longer spans are
     * replaced first, each with the shortest spans first.
     */
    private function replaceSpansWithDescendants(): void
    {
        while (!$this->isStopped()) {
            $replaced = false;

            foreach ($this->descendantsWithTheSameLabel() as [$span, $descendant]) {
                // once shrinking is stopped, no further candidate is replayed
                if ($this->consider($this->withChoicesOfSpanInsteadOf($descendant, $span))) {
                    $replaced = true;

                    break;
                }
            }

            if (!$replaced) {
                return;
            }
        }
    }

    /**
     * The pairs of a span of the current sequence and a shorter span that it
     * contains and that has the same label, longer spans first, and for each
     * of them shorter descendants first.
     *
     * @return list<array{Span, Span}>
     */
    private function descendantsWithTheSameLabel(): array
    {
        $spansByLabel = [];

        foreach ($this->current->spans() as $span) {
            $spansByLabel[$span->label()][] = $span;
        }

        $pairs = [];

        foreach ($spansByLabel as $spans) {
            // the spans are in the order in which they were started, so the
            // spans that a span contains follow it
            foreach ($spans as $index => $span) {
                for ($next = $index + 1; isset($spans[$next]) && $spans[$next]->start() < $span->end(); $next++) {
                    if ($spans[$next]->end() <= $span->end() && $spans[$next]->length() < $span->length()) {
                        $pairs[] = [$span, $spans[$next]];
                    }
                }
            }
        }

        usort(
            $pairs,
            static function (array $a, array $b): int
            {
                if ($a[0]->length() !== $b[0]->length()) {
                    return $b[0]->length() <=> $a[0]->length();
                }

                if ($a[0]->start() !== $b[0]->start()) {
                    return $a[0]->start() <=> $b[0]->start();
                }

                return $a[1]->length() <=> $b[1]->length();
            },
        );

        return $pairs;
    }

    /**
     * A counterexample for a property such as "the sum of the elements of a
     * list does not exceed 1000" has a sum just above 1000 once as many of its
     * elements as possible were deleted: deleting or lowering any element
     * then makes the property hold. It has fewer elements when the values of the earlier
     * elements are moved into the later ones, as far as these can take them,
     * so that the earlier ones become zero and can be deleted. A value is the
     * choice of a span that consists of one choice, and it is moved into the
     * values whose spans have the same label, the last ones first, wherever
     * they are: the elements of a list, or the parameters that are generated
     * alike. How much a value can take is known from the largest choice that
     * it could have been.
     */
    private function moveValuesToLaterValuesWithTheSameLabel(): void
    {
        $groups = $this->valuesWithTheSameLabel();

        foreach ($groups as $indexes) {
            foreach ($indexes as $position => $first) {
                foreach (array_reverse(array_slice($indexes, $position + 1)) as $second) {
                    if ($this->isStopped() || $this->choiceAt($first) === 0) {
                        break;
                    }

                    $room = $this->room($second);

                    if ($room === 0) {
                        continue;
                    }

                    if (!$this->consider($this->withMovedAmount($first, $second, min($this->choiceAt($first), $room)))) {
                        break;
                    }

                    // the values are found again in the next round when
                    // moving a value changed which choices are values
                    if ($this->valuesWithTheSameLabel() !== $groups) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * The indexes of the choices of spans that consist of one choice, grouped
     * by the label of the spans, for the labels of at least two such spans
     * of which no two are adjacent. Adjacent values with the same label are
     * the characters of a string, for instance, whose value would be moved
     * from character to character, and redistributeChoices() moves values
     * between adjacent choices anyway.
     *
     * @return list<non-empty-list<non-negative-int>>
     */
    private function valuesWithTheSameLabel(): array
    {
        $indexes = [];

        foreach ($this->current->spans() as $span) {
            if ($span->length() === 1) {
                $indexes[$span->label()][$span->start()] = $span->start();
            }
        }

        $groups = [];

        foreach ($indexes as $group) {
            if (count($group) < 2) {
                continue;
            }

            sort($group);

            $previous = null;

            foreach ($group as $index) {
                if ($previous !== null && $index === $previous + 1) {
                    continue 2;
                }

                $previous = $index;
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * How much the choice at the index can be raised without exceeding the
     * largest choice it can be.
     *
     * @param non-negative-int $index
     *
     * @return non-negative-int
     */
    private function room(int $index): int
    {
        $maximum = $this->current->maximumAt($index);

        if ($maximum === null) {
            $maximum = PHP_INT_MAX;
        }

        return max(0, $maximum - $this->choiceAt($index));
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
     * A counterexample for a property such as $a * $b < 1000, with $a and $b
     * between 0 and 100, cannot be shrunk from [20, 50] to [10, 100] by
     * minimizing one choice at a time or by moving an amount from $a to $b,
     * which keeps their sum. Lowering a choice by one can keep the property
     * falsified when a later choice is raised to the largest value it can
     * have, which the choice source clamps a larger choice to, and from which
     * the other passes lower it again.
     */
    private function lowerChoicesRaisingLaterOnes(): void
    {
        for ($index = 0; $index < $this->length() && !$this->isStopped(); $index++) {
            for ($later = $index + 1; $later <= $index + self::LARGEST_RAISING_DISTANCE && $later < $this->length(); $later++) {
                if ($this->choiceAt($index) === 0) {
                    break;
                }

                if ($this->consider($this->withLoweredAndRaisedChoice($index, $later))) {
                    break;
                }
            }
        }
    }

    /**
     * A choice can determine how many values are drawn after it, for instance
     * the size of a list that is drawn with Generators::lists($elements,
     * $size, $size) followed by an index into it. Lowering such a choice alone
     * makes the choices of the elements that are no longer drawn be read as
     * the index, and deleting the choices of elements alone makes the list
     * read the choice of the index as an element. The choice is therefore
     * lowered by an amount while as many of the last spans of a group of
     * spans that are part of the same span, have the same label, and follow
     * the choice are deleted: the elements of the list that the lower choice
     * no longer draws. The amount is doubled after each success and starts
     * over at one after a failure.
     */
    private function lowerChoicesDeletingLaterSpans(): void
    {
        $groups = $this->groupsOfSpans();

        for ($index = 0; $index < $this->length() && !$this->isStopped(); $index++) {
            $amount = 1;

            while ($index < $this->length() && $this->choiceAt($index) > 0) {
                if ($this->lowerChoiceDeletingLaterSpans($index, $amount, $groups)) {
                    $groups = $this->groupsOfSpans();
                    $amount *= 2;

                    continue;
                }

                if ($amount === 1) {
                    break;
                }

                $amount = 1;
            }
        }
    }

    /**
     * @param non-negative-int           $index
     * @param positive-int               $amount
     * @param list<non-empty-list<Span>> $groups the groups of spans of the current sequence (see groupsOfSpans())
     *
     * @return bool whether the choice was lowered
     */
    private function lowerChoiceDeletingLaterSpans(int $index, int $amount, array $groups): bool
    {
        $choice = $this->choiceAt($index);

        foreach ($this->groupsOfSpansAfter($index, $groups) as $spans) {
            $deleted = min($amount, $choice, count($spans));

            assert($deleted > 0);

            $lastSpans        = array_slice($spans, -$deleted);
            $withoutLastSpans = $this->withLoweredChoiceAndWithoutSpans($index, $deleted, $lastSpans);

            // the first spans are deleted when deleting the last ones does not
            // keep the property falsified, because the value that falsifies
            // it is one of the last ones, the last character of a string, for
            // instance
            if ($this->consider($withoutLastSpans) ||
                $this->consider($this->withLoweredChoiceAndWithoutSpans($index, $deleted, array_slice($spans, 0, $deleted)))) {
                return true;
            }

            // what the deleted spans contributed to the counterexample can be
            // needed to keep the property falsified, the length of a domain
            // name, for instance, and can be moved to a later group of spans
            // of the same kind by raising the choice that determines how
            // many spans it has
            $deletedChoices = array_sum(array_map(static fn (Span $span): int => $span->length(), $lastSpans));

            foreach ($this->choicesThatDetermineGroupsAfter(end($spans)->end(), $spans[0]->label(), $groups) as $later) {
                $raised = $later - $deletedChoices;

                // the deleted spans precede the raised choice
                assert($raised >= 0);

                if ($this->consider($this->withRaisedChoice($withoutLastSpans, $raised, $later, $deleted))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * The indexes of the choices that are followed by the first span of a
     * group of spans with the label, that are at most LARGEST_RAISING_DISTANCE
     * choices after the index, and that can be raised: the choices that
     * determine how many spans these groups have, such as the length of the
     * top-level domain of a domain name.
     *
     * @param non-negative-int           $index
     * @param non-empty-string           $label
     * @param list<non-empty-list<Span>> $groups
     *
     * @return list<non-negative-int>
     */
    private function choicesThatDetermineGroupsAfter(int $index, string $label, array $groups): array
    {
        $choices = [];

        foreach ($groups as $group) {
            $choice = $group[0]->start() - 1;

            // a choice that is as large as it can be cannot be raised
            if ($group[0]->label() === $label && $choice >= $index && $choice < $index + self::LARGEST_RAISING_DISTANCE && $this->room($choice) > 0) {
                $choices[] = $choice;
            }
        }

        sort($choices);

        return $choices;
    }

    /**
     * The spans of the current sequence grouped by the span they are part of
     * and by their label, each group in order.
     *
     * @return list<non-empty-list<Span>>
     */
    private function groupsOfSpans(): array
    {
        $groups = [];

        foreach ($this->current->spans() as $span) {
            $groups[$span->parent() . ':' . $span->label()][] = $span;
        }

        return array_values($groups);
    }

    /**
     * The groups of spans, with only the spans that start after the choice at
     * the index, the groups whose first such span is nearest to the choice
     * first. A group of which a span contains the choice is left out, because
     * the choice is part of one of the values it groups rather than a choice
     * that determines how many values it has. So is a group with fewer than
     * two spans after the choice: such a span is mostly that of a value that
     * is composed of others, a tuple or a list as a whole, rather than one of
     * several values that are drawn the same way.
     *
     * @param non-negative-int           $index
     * @param list<non-empty-list<Span>> $groups
     *
     * @return list<non-empty-list<Span>>
     */
    private function groupsOfSpansAfter(int $index, array $groups): array
    {
        $groupsAfter = [];

        foreach ($groups as $group) {
            $after = [];

            foreach ($group as $span) {
                if ($span->start() <= $index && $index < $span->end()) {
                    continue 2;
                }

                if ($span->start() > $index) {
                    $after[] = $span;
                }
            }

            if (count($after) > 1) {
                $groupsAfter[] = $after;
            }
        }

        usort(
            $groupsAfter,
            static fn (array $a, array $b): int => $a[0]->start() <=> $b[0]->start(),
        );

        return array_slice($groupsAfter, 0, self::LARGEST_NUMBER_OF_GROUPS_AFTER_A_CHOICE);
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
     * A counterexample for a property such as "a list does not contain both
     * [5, 6] and [1, 2]" can have its elements in any order, and the values
     * of parameters that are generated the same way can be swapped, too. The
     * spans of these values have the same label and are part of the same
     * span, or of none. They are sorted, so that the counterexample becomes
     * simpler, first all of them and then adjacent ones that are out of order.
     */
    private function sortSiblingSpans(): void
    {
        while (!$this->isStopped()) {
            $sorted = false;

            foreach ($this->siblingSpansWithTheSameLabel() as $siblings) {
                if ($this->sortSiblings($siblings)) {
                    $sorted = true;

                    break;
                }
            }

            if (!$sorted) {
                return;
            }
        }
    }

    /**
     * @return list<non-empty-list<Span>> the spans of the current sequence that have the same label and are part of the same span, in order, where there are at least two of them
     */
    private function siblingSpansWithTheSameLabel(): array
    {
        $groups = [];

        foreach ($this->current->spans() as $span) {
            $groups[$span->parent() . ':' . $span->label()][] = $span;
        }

        $siblings = [];

        foreach ($groups as $group) {
            if (count($group) > 1) {
                $siblings[] = $group;
            }
        }

        return $siblings;
    }

    /**
     * Considers the siblings in sorted order and, if that does not keep the
     * property falsified, with adjacent siblings that are out of order
     * swapped.
     *
     * @param non-empty-list<Span> $siblings
     *
     * @return bool whether the siblings were reordered
     */
    private function sortSiblings(array $siblings): bool
    {
        $choices = $this->current->choices();
        $slices  = [];

        foreach ($siblings as $sibling) {
            $slices[] = array_slice($choices, $sibling->start(), $sibling->length());
        }

        $sorted = $slices;

        usort($sorted, self::compareSlices(...));

        if ($sorted !== $slices && $this->consider($this->withSiblingsReplacedBy($siblings, $sorted))) {
            return true;
        }

        $previous = null;

        foreach ($slices as $position => $slice) {
            if ($previous !== null && self::compareSlices($previous, $slice) > 0) {
                $swapped = [...array_slice($slices, 0, $position - 1), $slice, $previous, ...array_slice($slices, $position + 1)];

                if ($this->consider($this->withSiblingsReplacedBy($siblings, $swapped))) {
                    return true;
                }
            }

            $previous = $slice;
        }

        return false;
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
     * @param non-empty-list<Span>                   $siblings spans that do not overlap, in order
     * @param non-empty-list<list<non-negative-int>> $slices   the choices that replace those of the siblings, in the same order
     *
     * @return list<non-negative-int>
     */
    private function withSiblingsReplacedBy(array $siblings, array $slices): array
    {
        $choices   = $this->current->choices();
        $reordered = [];
        $end       = 0;

        // the choices before, between, and after the siblings stay where they
        // are
        $replacements = array_map(
            static fn (Span $sibling, array $slice): array => ['sibling' => $sibling, 'slice' => $slice],
            $siblings,
            $slices,
        );

        foreach ($replacements as $replacement) {
            $reordered = [...$reordered, ...array_slice($choices, $end, $replacement['sibling']->start() - $end), ...$replacement['slice']];
            $end       = $replacement['sibling']->end();
        }

        return [...$reordered, ...array_slice($choices, $end)];
    }

    /**
     * Lowers the choice at the index by the amount and deletes the spans.
     *
     * @param non-negative-int $index
     * @param positive-int     $amount
     * @param list<Span>       $spans  spans that follow the choice and do not overlap, in order
     *
     * @return list<non-negative-int>
     */
    private function withLoweredChoiceAndWithoutSpans(int $index, int $amount, array $spans): array
    {
        $lowered = $this->choiceAt($index) - $amount;

        assert($lowered >= 0);

        $choices = $this->current->choices();

        // the spans are deleted from the last one, so that the positions of
        // the others do not change
        foreach (array_reverse($spans) as $span) {
            array_splice($choices, $span->start(), $span->length());
        }

        array_splice($choices, $index, 1, [$lowered]);

        return $choices;
    }

    /**
     * @param non-negative-int $index
     * @param non-negative-int $later
     *
     * @return list<non-negative-int>
     */
    private function withLoweredAndRaisedChoice(int $index, int $later): array
    {
        $lowered = $this->choiceAt($index) - 1;

        assert($lowered >= 0);

        $choices = $this->current->choices();

        array_splice($choices, $index, 1, [$lowered]);
        array_splice($choices, $later, 1, [PHP_INT_MAX]);

        return $choices;
    }

    /**
     * @return list<non-negative-int>
     */
    private function withChoicesOfSpanInsteadOf(Span $replacement, Span $span): array
    {
        $choices = $this->current->choices();

        array_splice($choices, $span->start(), $span->length(), array_slice($choices, $replacement->start(), $replacement->length()));

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
     * The choices with the choice at the index replaced by the choice at
     * $later of the current sequence, raised by the amount as far as it can
     * be raised.
     *
     * @param list<non-negative-int> $choices
     * @param non-negative-int       $index
     * @param non-negative-int       $later
     * @param positive-int           $amount
     *
     * @return list<non-negative-int>
     */
    private function withRaisedChoice(array $choices, int $index, int $later, int $amount): array
    {
        array_splice($choices, $index, 1, [$this->choiceAt($later) + min($amount, $this->room($later))]);

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

    /**
     * Compares the choices of two spans so that sorting spans by it makes the
     * choices they form together as simple as possible: a span comes first
     * when its choices followed by those of the other span are simpler than
     * the other way round.
     *
     * @param list<non-negative-int> $a
     * @param list<non-negative-int> $b
     */
    private static function compareSlices(array $a, array $b): int
    {
        return [...$a, ...$b] <=> [...$b, ...$a];
    }
}
