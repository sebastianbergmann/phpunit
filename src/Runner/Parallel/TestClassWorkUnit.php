<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function assert;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\IterativeTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\ResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheId;

/**
 * A unit of work that bundles all of the selected tests of a single test class,
 * kept together so that the class' shared fixtures (#[BeforeClass] /
 * #[AfterClass]) and intra-class ordering are preserved when the unit is run by
 * a single worker.
 *
 * A member of the unit is either a single test case or a suite that must
 * travel as one atomic member: an IterativeTestSuite, which aggregates the
 * repetitions of a repeated test method or the attempts of a retried test
 * method so that its repetition and retry orchestration runs inside the
 * worker, or a DataProviderTestSuite, which aggregates the tests of a data
 * provider method so that its event envelope is emitted inside the worker.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestClassWorkUnit implements WorkUnit
{
    /**
     * @var non-negative-int
     */
    private int $index;

    /**
     * @var class-string<TestCase>
     */
    private string $className;

    /**
     * @var list<DataProviderTestSuite|IterativeTestSuite|TestCase>
     */
    private array $tests;

    /**
     * @param non-negative-int                                        $index
     * @param class-string<TestCase>                                  $className
     * @param list<DataProviderTestSuite|IterativeTestSuite|TestCase> $tests
     */
    public function __construct(int $index, string $className, array $tests)
    {
        $this->index     = $index;
        $this->className = $className;
        $this->tests     = $tests;
    }

    /**
     * @return non-negative-int
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * @return class-string<TestCase>
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return list<DataProviderTestSuite|IterativeTestSuite|TestCase>
     */
    public function tests(): array
    {
        return $this->tests;
    }

    public function name(): string
    {
        return $this->className;
    }

    public function duration(ResultCache $resultCache): float
    {
        $duration = 0.0;

        foreach ($this->tests as $test) {
            $duration += $this->durationOf($test, $resultCache);
        }

        return $duration;
    }

    /**
     * The recorded duration of one member of the unit, with the members of an
     * aggregating suite — the tests of a data provider method, the attempts
     * of a retried test method, the repetitions of a repeated test method —
     * summed up recursively.
     */
    private function durationOf(Test $test, ResultCache $resultCache): float
    {
        if ($test instanceof TestCase) {
            return $resultCache->time(ResultCacheId::fromReorderable($test));
        }

        assert($test instanceof TestSuite);

        $duration = 0.0;

        foreach ($test->tests() as $aggregated) {
            $duration += $this->durationOf($aggregated, $resultCache);
        }

        return $duration;
    }
}
