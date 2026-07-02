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

use PHPUnit\Framework\IterativeTestSuite;
use PHPUnit\Framework\TestCase;

/**
 * A unit of work that bundles all of the selected tests of a single test class,
 * kept together so that the class' shared fixtures (#[BeforeClass] /
 * #[AfterClass]) and intra-class ordering are preserved when the unit is run by
 * a single worker.
 *
 * A member of the unit is either a single test case or an IterativeTestSuite
 * that aggregates the repetitions of a repeated test method or the attempts of
 * a retried test method; such a suite travels as one atomic member so that its
 * repetition and retry orchestration runs inside the worker.
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
     * @var list<IterativeTestSuite|TestCase>
     */
    private array $tests;

    /**
     * @param non-negative-int                  $index
     * @param class-string<TestCase>            $className
     * @param list<IterativeTestSuite|TestCase> $tests
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
     * @return list<IterativeTestSuite|TestCase>
     */
    public function tests(): array
    {
        return $this->tests;
    }

    public function name(): string
    {
        return $this->className;
    }
}
