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

use function array_column;
use function array_key_exists;
use function usort;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\ExecutionOrder\Direction;
use PHPUnit\Runner\ExecutionOrder\ReorderStage;

/**
 * Sorts tests small before medium before large before unknown.
 *
 * The size of a test suite is the size of the largest test it contains, so
 * that a test suite is never sorted before a test that is smaller than
 * everything the test suite contains.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class BySize implements ReorderStage
{
    /**
     * @var non-empty-array<non-empty-string, positive-int>
     */
    private const array SIZE_SORT_WEIGHT = [
        'small'   => 1,
        'medium'  => 2,
        'large'   => 3,
        'unknown' => 4,
    ];
    private readonly Direction $direction;

    /**
     * @var array<string, positive-int>
     */
    private array $weights = [];

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
        $weighted = [];

        foreach ($tests as $test) {
            $weighted[] = [$this->weight($test), $test];
        }

        if ($this->direction === Direction::Ascending) {
            usort($weighted, static fn (array $left, array $right) => $left[0] <=> $right[0]);
        } else {
            usort($weighted, static fn (array $left, array $right) => $right[0] <=> $left[0]);
        }

        return array_column($weighted, 1);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        if ($this->direction === Direction::Ascending) {
            return 'size-ascending';
        }

        return 'size-descending';
    }

    /**
     * @return positive-int
     */
    private function weight(Test $test): int
    {
        if ($test instanceof TestCase || $test instanceof DataProviderTestSuite) {
            return self::SIZE_SORT_WEIGHT[$test->size()->asString()];
        }

        if ($test instanceof TestSuite) {
            return $this->weightOfTestSuite($test);
        }

        return self::SIZE_SORT_WEIGHT['unknown'];
    }

    /**
     * The weight of a test suite is needed when its parent test suite is
     * reordered and again when the weight of that parent test suite is needed
     * one level further up. Remembering it keeps the tree from being walked
     * once per level.
     *
     * @return positive-int
     */
    private function weightOfTestSuite(TestSuite $testSuite): int
    {
        $sortId = $testSuite->sortId();

        if (array_key_exists($sortId, $this->weights)) {
            return $this->weights[$sortId];
        }

        $weight = 0;

        foreach ($testSuite->tests() as $test) {
            $innerWeight = $this->weight($test);

            if ($innerWeight > $weight) {
                $weight = $innerWeight;
            }
        }

        if ($weight === 0) {
            $weight = self::SIZE_SORT_WEIGHT['unknown'];
        }

        $this->weights[$sortId] = $weight;

        return $weight;
    }
}
