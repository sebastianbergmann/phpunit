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

use PHPUnit\Runner\TestSuiteSorter;

class TestSuiteSorterTest extends TestCase
{
    /**
     * @var TestSuite
     */
    private $suite;

    public function setup()
    {
        $this->suite = new TestSuite;
        $this->suite->addTestSuite(\MultiDependencyTest::class);
    }

    /**
     * @dataProvider suiteSorterOptionsProvider
     */
    public function testSuiteSorterOptions(int $order, bool $resolveDependencies, array $expected)
    {
        $sorter = new TestSuiteSorter();
        $sorter->reorderTestsInSuite($this->suite, $order, $resolveDependencies);

        $this->assertEquals($expected, $this->getTestExecutionOrder());
    }

    public function testSuitSorterRandomize()
    {
        \mt_srand(54321);
        $sorter = new TestSuiteSorter();
        $sorter->reorderTestsInSuite($this->suite, TestSuiteSorter::ORDER_RANDOMIZED, false);

        $this->assertEquals(['testTwo', 'testFour', 'testFive', 'testThree', 'testOne'], $this->getTestExecutionOrder());
    }

    public function testSuitSorterRandomizeResolve()
    {
        \mt_srand(54321);
        $sorter = new TestSuiteSorter();
        $sorter->reorderTestsInSuite($this->suite, TestSuiteSorter::ORDER_RANDOMIZED, true);

        $this->assertEquals(['testTwo', 'testFive', 'testOne', 'testThree', 'testFour'], $this->getTestExecutionOrder());
    }

    public function suiteSorterOptionsProvider(): array
    {
        return [
            'default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                false,
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],
            'resolve default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                true,
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],
            'reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                false,
                ['testFive', 'testFour', 'testThree', 'testTwo', 'testOne']],
            'resolve reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                true,
                ['testFive', 'testTwo', 'testOne', 'testThree', 'testFour']],
        ];
    }

    private function getTestExecutionOrder()
    {
        return \array_map(function ($test) {
            return $test->getName();
        }, $this->suite->tests()[0]->tests());
    }
}
