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
     * Whether a duration was recorded for the test. time() cannot tell: it
     * returns 0.0 for a test whose duration was not recorded, and 0.0 is also
     * what is recorded for a test that took less than half a millisecond, as
     * durations are recorded rounded to milliseconds (see
     * TestRunHistoryHandler).
     */
    public function hasTime(TestRunHistoryId $id): bool;

    public function load(): void;

    public function persist(): void;

    public function persistAndPrune(): void;
}
