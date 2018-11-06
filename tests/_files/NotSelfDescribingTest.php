<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;

class NotSelfDescribingTest implements Test
{
    public function log($msg): void
    {
        print $msg;
    }

    public function count(): int
    {
        return 0;
    }

    public function run(TestResult $result = null): TestResult
    {
        return new TestResult();
    }
}
