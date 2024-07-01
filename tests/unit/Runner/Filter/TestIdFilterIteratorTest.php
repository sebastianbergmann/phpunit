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

use function assert;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestSuiteIterator;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(TestIdFilterIterator::class)]
#[CoversClass(TestSuiteIterator::class)]
#[Small]
final class TestIdFilterIteratorTest extends TestCase
{
    public function testAcceptsTestsBasedOnTheirId(): void
    {
        $id = 'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative';

        foreach ($this->testSuiteIterator([$id]) as $test) {
            assert($test instanceof TestCase);

            $this->assertSame($id, $test->valueObjectForEvents()->id());
        }
    }

    /**
     * @param list<non-empty-string> $testIds
     */
    private function testSuiteIterator(array $testIds): Iterator
    {
        $factory = new Factory;

        $factory->addTestIdFilter($testIds);

        $testSuite = $this->testSuite();

        $testSuite->injectFilter($factory);

        return $testSuite->getIterator();
    }

    private function testSuite(): TestSuite
    {
        $suite = TestSuite::empty('test suite name');

        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'));
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative'));
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative2'));

        return $suite;
    }
}
