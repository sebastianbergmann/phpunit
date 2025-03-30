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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(ExcludeNameFilterIterator::class)]
#[Small]
class ExcludeNameFilterIteratorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCaseSensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('NotMatchingPattern')->accept());
    }

    /**
     * @throws Exception
     */
    public function testCaseInsensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('notmatchingbankaccount')->accept());
    }

    /**
     * @throws Exception
     */
    private function createFilter(string $filter): ExcludeNameFilterIterator
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'));

        $iterator = new ExcludeNameFilterIterator($suite->getIterator(), $filter);

        $iterator->rewind();

        return $iterator;
    }
}
