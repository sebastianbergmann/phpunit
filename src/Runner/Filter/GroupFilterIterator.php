<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function array_map;
use function array_push;
use function array_values;
use function in_array;
use function spl_object_id;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class GroupFilterIterator extends RecursiveFilterIterator
{
    /**
     * @psalm-var list<int>
     */
    private readonly array $groupTests;

    /**
     * @psalm-param RecursiveIterator<int, Test> $iterator
     * @psalm-param list<non-empty-string> $groups
     */
    public function __construct(RecursiveIterator $iterator, array $groups, TestSuite $suite)
    {
        parent::__construct($iterator);

        $groupTests = [];

        foreach ($suite->groupDetails() as $group => $tests) {
            if (in_array((string) $group, $groups, true)) {
                $testHashes = array_map(
                    'spl_object_id',
                    $tests,
                );

                array_push($groupTests, ...$testHashes);
            }
        }

        $this->groupTests = array_values($groupTests);
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        return $this->doAccept(spl_object_id($test), $this->groupTests);
    }

    /**
     * @psalm-param list<int> $groupTests
     */
    abstract protected function doAccept(int $id, array $groupTests): bool;
}
