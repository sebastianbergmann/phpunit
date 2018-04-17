<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Test;

use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestWarningHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

final class Extension implements BeforeFirstTestHook, BeforeTestHook, AfterSuccessfulTestHook, AfterSkippedTestHook, AfterRiskyTestHook, AfterIncompleteTestHook, AfterTestErrorHook, AfterTestWarningHook, AfterTestFailureHook, AfterLastTestHook
{
    public function executeBeforeFirstTest(): void
    {
        print __METHOD__ . PHP_EOL;
    }

    public function executeBeforeTest(string $test): void
    {
        print __METHOD__ . ': ' . $test . PHP_EOL;
    }

    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        print __METHOD__ . ': ' . $test . PHP_EOL;
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        print __METHOD__ . ': ' . $test . ': ' . $message . PHP_EOL;
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        print __METHOD__ . ': ' . $test . ': ' . $message . PHP_EOL;
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        print __METHOD__ . ': ' . $test . ': ' . $message . PHP_EOL;
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        print __METHOD__ . ': ' . $test . ': ' . $message . PHP_EOL;
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        print __METHOD__ . ': ' . $test . ': ' . $message . PHP_EOL;
    }

    public function executeAfterTestWarning(string $test, string $message, float $time): void
    {
        print __METHOD__ . ': ' . $test . ': ' . $message . PHP_EOL;
    }

    public function executeAfterLastTest(): void
    {
        print __METHOD__ . PHP_EOL;
    }
}
