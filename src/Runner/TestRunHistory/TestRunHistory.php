<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface TestRunHistory
{
    public function setStatus(TestRunHistoryId $id, TestStatus $status): void;

    public function remove(TestRunHistoryId $id): void;

    public function status(TestRunHistoryId $id): TestStatus;

    public function setTime(TestRunHistoryId $id, float $time): void;

    public function time(TestRunHistoryId $id): float;

    /**
     * Remembers the choice sequences of the counterexamples of a property that
     * was falsified, one for each way in which it was falsified, the minimal
     * counterexample that was reported first.
     *
     * @param non-empty-list<list<non-negative-int>> $choiceSequences
     */
    public function setCounterexamples(TestRunHistoryId $id, array $choiceSequences): void;

    public function removeCounterexamples(TestRunHistoryId $id): void;

    /**
     * Returns the choice sequences of the counterexamples of a property that
     * was falsified, and an empty list when none are remembered.
     *
     * @return list<list<non-negative-int>>
     */
    public function counterexamples(TestRunHistoryId $id): array;

    public function load(): void;

    public function persist(): void;

    public function persistAndPrune(): void;
}
