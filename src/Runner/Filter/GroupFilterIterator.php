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

use function array_merge;
use function array_push;
use function in_array;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class GroupFilterIterator extends RecursiveFilterIterator
{
    /**
     * @psalm-var list<non-empty-string>
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
            if (in_array($group, $groups, true)) {
                $groupTests = array_merge($groupTests, $tests);

                array_push($groupTests, ...$groupTests);
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
     * @psalm-param non-empty-string $id
     * @psalm-param list<non-empty-string> $groupTests
     */
    abstract protected function doAccept(string $id, array $groupTests): bool;
}
