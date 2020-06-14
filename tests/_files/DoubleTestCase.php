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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;

class DoubleTestCase implements Test
{
    protected $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function count()
    {
        return 2;
    }

    public function run(TestResult $result = null): TestResult
    {
        $result->startTest($this);

        $this->testCase->runBare();
        $this->testCase->runBare();

        $result->endTest($this, 0);

        return $result;
    }
}
