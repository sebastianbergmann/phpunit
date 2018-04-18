<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestSuite;

class StopOnWarningTestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Test Warnings');

        $suite->addTestSuite(NoTestCases::class);
        $suite->addTestSuite(CoverageClassTest::class);

        return $suite;
    }
}
