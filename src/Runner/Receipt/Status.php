<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

/**
 * How a test run ended.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
enum Status: string
{
    /**
     * Every selected test was run.
     */
    case Completed = 'completed';

    /**
     * A stopOn* setting ended the test run.
     */
    case Stopped = 'stopped';

    /**
     * A signal ended the test run.
     */
    case Interrupted = 'interrupted';

    /**
     * The time limit for the test run was exceeded.
     */
    case TimeLimitExceeded = 'time-limit-exceeded';

    /**
     * No test was run because the test run could not be started, for instance
     * because code coverage was requested and cannot be collected.
     */
    case NotStarted = 'not-started';
}
