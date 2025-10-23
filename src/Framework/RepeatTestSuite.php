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

use function count;
use PHPUnit\Event;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestRunner\TestResult\PassedTests;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RepeatTestSuite implements Reorderable, Test
{
    /**
     * @var non-empty-list<PhptTestCase>|non-empty-list<TestCase>
     */
    private array $tests;

    /**
     * @param positive-int $times
     */
    public function __construct(PhptTestCase|TestCase $test, int $times)
    {
        $tests = [];

        for ($i = 0; $i < $times; $i++) {
            $tests[] = clone $test;
        }

        $this->tests = $tests;
    }

    public function count(): int
    {
        return count($this->tests);
    }

    public function run(): void
    {
        if ($this->isPhptTestCase()) {
            $this->runPhptTestCase();
        } else {
            $this->runTestCase();
        }
    }

    public function sortId(): string
    {
        return $this->tests[0]->sortId();
    }

    public function provides(): array
    {
        return $this->tests[0]->provides();
    }

    public function requires(): array
    {
        return $this->tests[0]->requires();
    }

    public function nameWithDataSet(): string
    {
        return $this->tests[0]->nameWithDataSet();
    }

    public function valueObjectForEvents(): Event\Code\Phpt|Event\Code\TestMethod
    {
        return $this->tests[0]->valueObjectForEvents();
    }

    private function runTestCase(): void
    {
        $defectOccurred = false;

        foreach ($this->tests as $test) {
            if ($defectOccurred) {
                $test->markSkippedForErrorInPreviousRepetition();

                continue;
            }

            $test->run();

            if ($test->status()->isFailure() || $test->status()->isError()) {
                $defectOccurred = true;

                PassedTests::instance()->testMethodDidNotPass($test::class . '::' . $test->name());
            }
        }
    }

    private function runPhptTestCase(): void
    {
        $defectOccurred = false;

        foreach ($this->tests as $test) {
            if ($defectOccurred) {
                EventFacade::emitter()->testSkipped(
                    $this->valueObjectForEvents(),
                    'Test repetition failure',
                );

                continue;
            }

            $test->run();

            if (!$test->passed()) {
                $defectOccurred = true;
            }
        }
    }

    /**
     * @phpstan-assert-if-true non-empty-list<PhptTestCase> $this->tests
     */
    private function isPhptTestCase(): bool
    {
        return $this->tests[0] instanceof PhptTestCase;
    }
}
