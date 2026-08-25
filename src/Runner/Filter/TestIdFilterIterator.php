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

use function array_fill_keys;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
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
final class TestIdFilterIterator extends RecursiveFilterIterator
{
    /**
     * An empty list matches no test: a selection of tests that selected
     * nothing is not the same as no selection at all.
     *
     * The identifiers are kept as the keys of an array because there can be as
     * many of them as there are tests: test impact analysis hands the whole
     * selection to this filter, and looking each test up in a list would take
     * as long as the number of tests times the number of identifiers.
     *
     * @var array<non-empty-string, true>
     */
    private readonly array $testIds;

    /**
     * @param RecursiveIterator<int, Test> $iterator
     * @param list<non-empty-string>       $testIds
     */
    public function __construct(RecursiveIterator $iterator, array $testIds)
    {
        parent::__construct($iterator);

        $this->testIds = array_fill_keys($testIds, true);
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        if (!$test instanceof TestCase && !$test instanceof PhptTestCase) {
            return false;
        }

        try {
            return isset($this->testIds[$test->valueObjectForEvents()->id()]);
            // @codeCoverageIgnoreStart
        } catch (NoDataSetFromDataProviderException) {
            return false;
            // @codeCoverageIgnoreEnd
        }
    }
}
