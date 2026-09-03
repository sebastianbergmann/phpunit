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
final readonly class BySize implements ReorderStage
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
    private Direction $direction;

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
            $max = 0;

            foreach ($test->tests() as $inner) {
                $weight = $this->weight($inner);

                if ($weight > $max) {
                    $max = $weight;
                }
            }

            if ($max > 0) {
                return $max;
            }
        }

        return self::SIZE_SORT_WEIGHT['unknown'];
    }
}
