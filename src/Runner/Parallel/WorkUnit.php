<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use PHPUnit\Runner\TestRunHistory\TestRunHistory;

/**
 * A unit of work distributed to a worker process.
 *
 * The index records the unit's position in the deterministic suite order; the
 * ResultAggregator uses it to release the units' results in that order even
 * though the workers finish them out of order. The name is a human-readable
 * label used when a unit has to be reported on, for instance after a crash.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface WorkUnit
{
    /**
     * @return non-negative-int
     */
    public function index(): int;

    public function name(): string;

    /**
     * The estimated duration of this unit's tests, in seconds: what a previous
     * run recorded for them, and for the tests that have not run before what
     * the size they declare promises (see TestClassWorkUnit); 0.0 when neither
     * is available. The scheduler dispatches the units of a chunk longest first
     * (see Scheduler).
     */
    public function duration(TestRunHistory $testRunHistory): float;
}
