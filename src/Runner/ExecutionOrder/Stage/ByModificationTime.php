<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder\Stage;

use function array_key_exists;
use function filemtime;
use function is_file;
use function max;
use function usort;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\ExecutionOrder\Direction;
use PHPUnit\Runner\ExecutionOrder\ReorderStage;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Util\Reflection;
use ReflectionClass;

/**
 * Sorts tests by the time their source files were last modified.
 *
 * The source files of a test are the files a change to which can change what
 * the test does: the file that declares the test class, the files that declare
 * the classes it extends, and the files that declare the traits any of them
 * use. The most recent modification among them decides. For a PHPT test, the
 * .phpt file is the test.
 *
 * The modification time of a test suite is the most recent modification time
 * among the tests it contains, so that a test suite is as new as the newest
 * test in it. This propagates upwards through arbitrarily deeply nested test
 * suites.
 *
 * A test whose modification time cannot be determined weighs zero, just like a
 * test that ByDuration knows nothing about, and is therefore sorted last when
 * sorting from newest to oldest.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ByModificationTime implements ReorderStage
{
    private readonly Direction $direction;

    /**
     * @var array<string, non-negative-int>
     */
    private array $weights = [];

    /**
     * @var array<class-string, non-negative-int>
     */
    private array $weightsOfClasses = [];

    /**
     * @var array<non-empty-string, non-negative-int>
     */
    private array $modificationTimes = [];

    public function __construct(Direction $direction)
    {
        $this->direction = $direction;
    }

    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    public function apply(array $tests, Context $context): array
    {
        if ($this->direction === Direction::Ascending) {
            usort(
                $tests,
                fn (Test $left, Test $right) => $this->weight($left) <=> $this->weight($right),
            );

            return $tests;
        }

        usort(
            $tests,
            fn (Test $left, Test $right) => $this->weight($right) <=> $this->weight($left),
        );

        return $tests;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        if ($this->direction === Direction::Ascending) {
            return 'modified-ascending';
        }

        return 'modified-descending';
    }

    /**
     * @return non-negative-int
     */
    private function weight(Test $test): int
    {
        if ($test instanceof TestSuite) {
            return $this->weightOfTestSuite($test);
        }

        if ($test instanceof PhptTestCase) {
            return $this->modificationTime($test->file());
        }

        if ($test instanceof TestCase) {
            return $this->weightOfClass($test::class);
        }

        // @codeCoverageIgnoreStart
        return 0;
        // @codeCoverageIgnoreEnd
    }

    /**
     * The tests of a test suite are reordered before the test suite itself is
     * reordered within its parent, so the weight of a test suite that is nested
     * in another one has usually been computed before it is needed. Remembering
     * it keeps the tree from being walked once per level.
     *
     * @return non-negative-int
     */
    private function weightOfTestSuite(TestSuite $testSuite): int
    {
        $sortId = $testSuite->sortId();

        if (array_key_exists($sortId, $this->weights)) {
            return $this->weights[$sortId];
        }

        $weight = 0;

        foreach ($testSuite->tests() as $test) {
            $weight = max($weight, $this->weight($test));
        }

        $this->weights[$sortId] = $weight;

        return $weight;
    }

    /**
     * @param class-string<TestCase> $className
     *
     * @return non-negative-int
     */
    private function weightOfClass(string $className): int
    {
        if (array_key_exists($className, $this->weightsOfClasses)) {
            return $this->weightsOfClasses[$className];
        }

        $weight = 0;

        foreach (Reflection::sourceFilesOf(new ReflectionClass($className)) as $file) {
            $weight = max($weight, $this->modificationTime($file));
        }

        $this->weightsOfClasses[$className] = $weight;

        return $weight;
    }

    /**
     * Returns zero when the file does not exist or cannot be stat'ed.
     *
     * @param non-empty-string $file
     *
     * @return non-negative-int
     */
    private function modificationTime(string $file): int
    {
        if (array_key_exists($file, $this->modificationTimes)) {
            return $this->modificationTimes[$file];
        }

        $modificationTime = 0;

        if (is_file($file)) {
            $result = @filemtime($file);

            if ($result !== false && $result > 0) {
                $modificationTime = $result;
            }
        }

        $this->modificationTimes[$file] = $modificationTime;

        return $modificationTime;
    }
}
