<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * A Listener for test progress.
 */
interface TestRunAwareTestListener extends TestListener
{
    /**
     * A test run started.
     */
    public function startTestRun(TestSuite $suite): void;

    /**
     * A test run ended.
     */
    public function endTestRun(TestSuite $suite, TestResult $result): void;
}
