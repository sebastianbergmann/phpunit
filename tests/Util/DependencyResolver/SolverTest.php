<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\DependencyResolver;

use PHPUnit\Framework\DependentTestInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Util\DependencyResolver\Stub\StubTestCase;

class SolverTest extends TestCase
{
    /** @var Solver */
    protected $solver;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->solver = new Solver();
    }

    /**
     * @dataProvider dependsTestProvider
     *
     * @param TestSuite $testSuite
     * @param array $expectedOrderTests
     */
    public function testResolve(TestSuite $testSuite, array $expectedOrderTests)
    {
        $this->solver->resolve($testSuite);

        $getTestList = function (TestSuite $testSuite) use (&$getTestList) {
            $testList = [];
            foreach ($testSuite->tests() as $test) {
                if ($test instanceof TestSuite) {
                    $testList = array_merge($testList, $getTestList($test));
                } elseif ($test instanceof DependentTestInterface) {
                    $testList[] = $test->getName();
                }
            }

            return $testList;
        };

        $testList = $getTestList($testSuite);

        foreach ($expectedOrderTests as $test) {
            $first = array_search($test[0], $testList);
            $second = array_search($test[1], $testList);
            $this->assertTrue($first < $second);
        }
    }

    public function dependsTestProvider()
    {
        yield 'not depend tests' => [
            'testSuite' => $this->createTestSuite(
                'test1',
                ['testA' => [], 'testB' => [], 'testC' => []]
            ),
            'expectedOrderTests' => [
                ['testA', 'testB'],
                ['testB', 'testC']
            ]
        ];

        yield 'depend tests one' => [
            'testSuite' => $this->createTestSuite(
                'test1',
                ['testA' => ['testC'], 'testB' => ['testA'], 'testC' => []]
            ),
            'expectedOrderTests' => [
                ['testA', 'testB'],
                ['testC', 'testA']
            ]
        ];

        yield 'depend tests two' => [
            'testSuite' => $this->createTestSuite(
                'test1',
                [
                    'testA' => ['testC', 'testB', 'testD'],
                    'testB' => [],
                    'testC' => ['testD'],
                    'testD' => ['testB']
                ]
            ),
            'expectedOrderTests' => [
                ['testB', 'testD'],
                ['testB', 'testA'],
                ['testB', 'testC'],
                ['testC', 'testA']
            ]
        ];

        yield 'depend test suite' => [
            'testSuite' => $this->createTestSuite(
                'test1',
                [
                    'testA' => ['testC'],
                    'testB' => $this->createTestSuite('testB', [
                        'testB1' => ['testA'],
                        'testB2' => ['testA']
                    ]),
                    'testC' => []
                ]
            ),
            'expectedOrderTests' => [
                ['testA', 'testB1'],
                ['testA', 'testB2'],
                ['testC', 'testA']
            ]
        ];
    }

    /**
     * @param string $name
     * @param array $tests
     * @return TestSuite
     */
    protected function createTestSuite($name, array $tests)
    {
        $testSuite = new TestSuite('', $name);
        foreach ($tests as $name => $dependencies) {
            if ($dependencies instanceof TestSuite) {
                $testSuite->addTest($dependencies);
            } else {
                $testSuite->addTest(new StubTestCase($name, $dependencies));
            }
        }

        return $testSuite;
    }
}
