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

use function mt_srand;
use PHPUnit\Event;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\ReorderPipeline;
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

            if ($configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
                mt_srand($configuration->randomOrderSeed());
            }

            $testRunHistory->load();

            $pipeline = ReorderPipeline::fromConfiguration(
                $configuration->executionOrder(),
                $configuration->executionOrderDefects(),
                $configuration->resolveDependencies(),
            );

            if (!$pipeline->isEmpty()) {
                new TestSuiteSorter($testRunHistory)->apply($suite, $pipeline);

                $this->emitter->testSuiteSorted(
                    $configuration->executionOrder(),
                    $configuration->executionOrderDefects(),
                    $configuration->resolveDependencies(),
                    $pipeline->describe(),
                );
            }

            (new TestSuiteFilterProcessor)->process($configuration, $suite);

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
}
