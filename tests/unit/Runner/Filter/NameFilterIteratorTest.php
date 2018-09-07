<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class NameFilterIteratorTest extends TestCase
{
    public function testCaseSensitiveMatch()
    {
        $this->assertTrue($this->createFilter('BankAccountTest')->accept());
    }

    public function testCaseInsensitiveMatch()
    {
        $this->assertTrue($this->createFilter('bankaccounttest')->accept());
    }

    private function createFilter(string $filter): NameFilterIterator
    {
        $suite = new TestSuite;
        $suite->addTest(new \BankAccountTest('testBalanceIsInitiallyZero'));

        $iterator = new NameFilterIterator($suite->getIterator(), $filter);

        $iterator->rewind();

        return $iterator;
    }
}
