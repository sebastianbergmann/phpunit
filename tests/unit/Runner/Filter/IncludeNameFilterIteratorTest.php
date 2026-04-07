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

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(IncludeNameFilterIterator::class)]
#[CoversClass(NameFilterIterator::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/filter')]
final class IncludeNameFilterIteratorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCaseSensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('BankAccountTest')->accept());
    }

    /**
     * @throws Exception
     */
    public function testCaseInsensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('bankaccounttest')->accept());
    }

    /**
     * @throws Exception
     */
    public function testHashWithNamedDataSetMatches(): void
    {
        $this->assertTrue($this->createFilterWithNamedDataSet('testBalanceIsInitiallyZero#my data set', 'my data set')->accept());
    }

    /**
     * @throws Exception
     */
    public function testHashWithNamedDataSetDoesNotMatchDifferentName(): void
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setData('my data set', [1]);

        $suite = TestSuite::empty('test suite name');
        $suite->addTest($test);

        $factory = new Factory;
        $factory->addIncludeNameFilter('testBalanceIsInitiallyZero#other');
        $suite->injectFilter($factory);

        $count = 0;

        foreach ($suite->getIterator() as $_test) {
            $count++;
        }

        $this->assertSame(0, $count);
    }

    /**
     * @throws Exception
     */
    public function testHashWithNumericDataSetStillWorks(): void
    {
        $this->assertTrue($this->createFilterWithNumericDataSet('testBalanceIsInitiallyZero#0', 0)->accept());
    }

    /**
     * @throws Exception
     */
    public function testAtSignWithNamedDataSetStillWorks(): void
    {
        $this->assertTrue($this->createFilterWithNamedDataSet('testBalanceIsInitiallyZero@my data set', 'my data set')->accept());
    }

    /**
     * @throws Exception
     */
    private function createFilter(string $filter): IncludeNameFilterIterator
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'));

        $iterator = new IncludeNameFilterIterator($suite->getIterator(), $filter);

        $iterator->rewind();

        return $iterator;
    }

    /**
     * @throws Exception
     */
    private function createFilterWithNamedDataSet(string $filter, string $dataSetName): IncludeNameFilterIterator
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setData($dataSetName, [1]);

        $suite = TestSuite::empty('test suite name');
        $suite->addTest($test);

        $iterator = new IncludeNameFilterIterator($suite->getIterator(), $filter);

        $iterator->rewind();

        return $iterator;
    }

    /**
     * @throws Exception
     */
    private function createFilterWithNumericDataSet(string $filter, int $dataSetIndex): IncludeNameFilterIterator
    {
        $test = new BankAccountTest('testBalanceIsInitiallyZero');
        $test->setData($dataSetIndex, [1]);

        $suite = TestSuite::empty('test suite name');
        $suite->addTest($test);

        $iterator = new IncludeNameFilterIterator($suite->getIterator(), $filter);

        $iterator->rewind();

        return $iterator;
    }
}
