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

class TestResultTest extends TestCase
{
    public function canSkipCoverageProvider()
    {
        return [
            ['CoverageClassTest', true],
            ['CoverageNothingTest', true],
            ['CoverageCoversOverridesCoversNothingTest', false],
        ];
    }

    /**
     * @dataProvider canSkipCoverageProvider
     */
    public function testCanSkipCoverage($testCase, $expectedCanSkip)
    {
        require_once __DIR__ . '/../_files/' . $testCase . '.php';

        $test            = new $testCase();
        $canSkipCoverage = TestResult::isAnyCoverageRequired($test);
        $this->assertEquals($expectedCanSkip, $canSkipCoverage);
    }
}
