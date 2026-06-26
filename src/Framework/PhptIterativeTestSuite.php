<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Event;
use PHPUnit\Event\EventCollection;

/**
 * A test suite that aggregates the repetitions or attempts of a single PHPT
 * test. Unlike the repetitions and attempts of a test method, a PHPT test does
 * not track its own status, so the outcome of each run is determined from the
 * events it emits.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class PhptIterativeTestSuite extends IterativeTestSuite
{
    /**
     * @var non-empty-string
     */
    protected string $filename;

    /**
     * Whether the events collected for a single run of a PHPT test indicate
     * that the run failed or errored.
     */
    final protected function failedOrErrored(EventCollection $events): bool
    {
        foreach ($events as $event) {
            if ($event instanceof Event\Test\Failed || $event instanceof Event\Test\Errored) {
                return true;
            }
        }

        return false;
    }
}
