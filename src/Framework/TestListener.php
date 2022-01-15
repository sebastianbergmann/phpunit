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

use Throwable;

/**
 * This interface, as well as the associated mechanism for extending PHPUnit,
 * will be removed in PHPUnit 10. There is no alternative available in this
 * version of PHPUnit.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @deprecated
 * @see https://github.com/sebastianbergmann/phpunit/issues/4676
 */
interface TestListener
{
    public function addError(Test $test, Throwable $t, float $time): void;

    public function addWarning(Test $test, Warning $e, float $time): void;

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void;

    public function addIncompleteTest(Test $test, Throwable $t, float $time): void;

    public function addRiskyTest(Test $test, Throwable $t, float $time): void;

    public function addSkippedTest(Test $test, Throwable $t, float $time): void;

    public function startTestSuite(TestSuite $suite): void;

    public function endTestSuite(TestSuite $suite): void;

    public function startTest(Test $test): void;

    public function endTest(Test $test, float $time): void;
}
