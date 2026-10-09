<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use PHPUnit\Event;
use PHPUnit\Framework\PropertyTestSuite;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\ReorderPipeline;
use PHPUnit\Runner\RandomNumberGeneratorSeeder;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\Configuration\Configuration;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private readonly Event\Emitter $emitter;

    public function __construct(Event\Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * @throws RuntimeException
     */
    public function run(Configuration $configuration, TestRunHistory $testRunHistory, TestSuite $suite): void
    {
        try {
            $this->emitter->testRunnerStarted();

            new RandomNumberGeneratorSeeder($configuration)->seed();

            $testRunHistory->load();

            $this->provideTestRunHistoryToProperties($suite, $testRunHistory);

            $pipeline = ReorderPipeline::fromConfiguration(
                $configuration->executionOrder(),
                $configuration->resolveDependencies(),
            );

            if (!$pipeline->isEmpty()) {
                new TestSuiteSorter($testRunHistory)->apply($suite, $pipeline);

                $this->emitter->testSuiteSorted($pipeline->describe());
            }

            new TestSuiteFilterProcessor($this->emitter)->process($configuration, $suite);

            $this->emitter->testRunnerExecutionStarted(
                Event\TestSuite\TestSuiteBuilder::from($suite),
            );

            $suite->run();

            $this->emitter->testRunnerExecutionFinished();
            $this->emitter->testRunnerFinished();
        } catch (Throwable $t) {
            throw new RuntimeException(
                $t->getMessage(),
                (int) $t->getCode(),
                $t,
            );
        }
    }

    /**
     * A property checks the counterexample that was remembered in the test
     * run history when it was last falsified before any other example.
     */
    private function provideTestRunHistoryToProperties(TestSuite $suite, TestRunHistory $testRunHistory): void
    {
        foreach ($suite->tests() as $test) {
            if ($test instanceof PropertyTestSuite) {
                $test->setTestRunHistory($testRunHistory);

                continue;
            }

            if ($test instanceof TestSuite) {
                $this->provideTestRunHistoryToProperties($test, $testRunHistory);
            }
        }
    }
}
