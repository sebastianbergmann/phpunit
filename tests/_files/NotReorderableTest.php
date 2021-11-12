<?php declare(strict_types=1);
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
use PHPUnit\TextUI\TestRunner;

class NotReorderableTest implements Test
{
    public function count(): int
    {
        return 1;
    }

    public function run(TestResult $result = null, TestRunner $runner = null): TestResult
    {
        $testResult = new TestResult();
        $testResult->setRunner($runner);

        return $testResult;
    }

    public function provides(): array
    {
        return [];
    }

    public function requires(): array
    {
        return [];
    }
}
