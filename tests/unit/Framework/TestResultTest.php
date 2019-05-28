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

/**
 * @small
 */
final class TestResultTest extends TestCase
{
    /**
     * @dataProvider canSkipCoverageProvider
     */
    public function testCanSkipCoverage($testCase, $expectedCanSkip): void
    {
        require_once TEST_FILES_PATH . $testCase . '.php';

        $test             = new $testCase('testSomething');
        $coverageRequired = TestResult::isAnyCoverageRequired($test);
        $canSkipCoverage  = !$coverageRequired;

        $this->assertEquals($expectedCanSkip, $canSkipCoverage);
    }

    public function canSkipCoverageProvider(): array
    {
        return [
            ['CoverageClassTest', false],
            ['CoverageClassWithoutAnnotationsTest', false],
            ['CoverageCoversOverridesCoversNothingTest', false],
            ['CoverageClassNothingTest', true],
            ['CoverageMethodNothingTest', true],
        ];
    }
}
