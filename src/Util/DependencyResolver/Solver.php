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
use PHPUnit\Framework\TestSuite;

class Solver
{
    /**
     * @param TestSuite $testSuite
     */
    public function resolve(TestSuite $testSuite)
    {
        $this->resolveDependency($testSuite);
    }

    /**
     * @param TestSuite $testSuite
     * @return Problem
     */
    protected function resolveDependency(TestSuite $testSuite)
    {
        $problems = [];

        foreach ($testSuite->tests() as $test) {
            if ($test instanceof TestSuite) {
                $problems[] = $this->resolveDependency($test);
                continue;
            }

            if ($test instanceof DependentTestInterface) {
                $problems[] = new Problem($test->getName(), $test, $test->getDependencies());
            } else {
                $testName = is_callable([$test, 'getName']) ? $test->getName() : spl_object_hash($test);
                $problems[] = new Problem($testName, $test);
            }
        }

        return $this->mergeProblems($testSuite, $problems);
    }

    /**
     * @param TestSuite $testSuite
     * @param Problem[] $problems
     * @return Problem
     */
    protected function mergeProblems(TestSuite $testSuite, array $problems)
    {
        list ($tests, $poolProblems, $inPool, $dependencies) = [[], [], [], []];
        foreach ($problems as $problem) {
            $poolProblems[$problem->getName()][] = $problem;
        }

        $resolver = function (
            Problem $problem,
            array $tests = []
        ) use (
            &$resolver,
            &$inPool,
            &$dependencies,
            $poolProblems
        ) {
            $inPool[$problem->getName()] = true;

            while (!$problem->isEmpty()) {
                $dependency = $problem->pop();
                $dependencies[] = $dependency;
                if (!array_key_exists($dependency, $inPool) && array_key_exists($dependency, $poolProblems)) {
                    /** @var Problem $element */
                    foreach ($poolProblems[$dependency] as $nextProblem) {
                        $tests = $resolver($nextProblem, $tests);
                    }
                }
            }

            if (!in_array($problem->getObject(), $tests, true)) {
                $tests[] = $problem->getObject();
            }

            return $tests;
        };

        foreach ($problems as $problem) {
            $tests = $resolver($problem, $tests);
        }
        $testSuite->setTests($tests);

        return new Problem($testSuite->getName(), $testSuite, array_unique($dependencies));
    }
}
