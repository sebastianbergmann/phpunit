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

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @extends RecursiveFilterIterator<int, Test, RecursiveIterator<int, Test>>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class GroupFilterIterator extends RecursiveFilterIterator
{
    /**
     * The identifiers of the tests that the selection selects are used as keys
     * so that looking one up does not become more expensive as more tests are
     * selected.
     *
     * @var array<non-empty-string, true>
     */
    private readonly array $groupTests;

    /**
     * @param RecursiveIterator<int, Test> $iterator
     * @param list<non-empty-string>       $groups
     */
    public function __construct(RecursiveIterator $iterator, array $groups, TestSuite $suite)
    {
        parent::__construct($iterator);

        $filter = CompiledGroupFilter::from($groups);

        /*
         * Only the groups the selection names can make a difference to it, so
         * only the tests in those groups are looked at. A test that is in none
         * of them cannot be selected, and the conjunction it would have to
         * match to be selected cannot be evaluated without knowing which of
         * those groups it is in.
         *
         * @var array<non-empty-string, non-empty-list<non-empty-string>>
         */
        $selectedGroupsOfTest = [];

        foreach ($suite->groups() as $group => $tests) {
            // the name of a group that is a number is an integer key
            $group = (string) $group;

            if (!$filter->mentions($group)) {
                continue;
            }

            foreach ($tests as $test) {
                $selectedGroupsOfTest[$test][] = $group;
            }
        }

        $groupTests = [];

        foreach ($selectedGroupsOfTest as $test => $groupsOfTest) {
            if ($filter->matches($groupsOfTest)) {
                $groupTests[$test] = true;
            }
        }

        $this->groupTests = $groupTests;
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        if ($test instanceof TestCase || $test instanceof PhptTestCase) {
            return $this->doAccept($test->valueObjectForEvents()->id(), $this->groupTests);
        }

        return true;
    }

    /**
     * @param non-empty-string              $id
     * @param array<non-empty-string, true> $groupTests
     */
    abstract protected function doAccept(string $id, array $groupTests): bool;
}
