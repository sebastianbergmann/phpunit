<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

/**
 * Why a test is run when only the tests that can be affected by what changed
 * are run.
 *
 * Only one of these is reported for a test, even where more than one applies:
 * the question a developer asks is why a test is run at all, and the first
 * answer settles it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This enumeration is not covered by the backward compatibility promise for PHPUnit
 */
enum SelectionReason
{
    /**
     * The test executed, or declared that it depends on, a file that is not
     * what it was when the test was recorded.
     */
    case DependsOnSomethingThatChanged;

    /**
     * Nothing was recorded for the test. It has never been run, or it was
     * skipped or marked incomplete when it was, and a test that executes
     * nothing has nothing to record.
     */
    case NothingIsKnownAboutIt;

    /**
     * The test did not pass when it was last run. What was recorded for it
     * describes a run that did not get through the test.
     */
    case ItDidNotPass;

    /**
     * Another test that is run depends on this test, and cannot be run
     * without it.
     */
    case AnotherTestDependsOnIt;

    /**
     * The test is not a test method and can therefore never be recorded.
     */
    case ItCannotBeRecorded;
}
