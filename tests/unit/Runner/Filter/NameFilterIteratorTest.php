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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(NameFilterIterator::class)]
#[Small]
final class NameFilterIteratorTest extends TestCase
{
    public function testCaseSensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('BankAccountTest')->accept());
    }

    public function testCaseInsensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('bankaccounttest')->accept());
    }

    private function createFilter(string $filter): NameFilterIterator
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'));

        $iterator = new NameFilterIterator($suite->getIterator(), $filter);

        $iterator->rewind();

        return $iterator;
    }
}
