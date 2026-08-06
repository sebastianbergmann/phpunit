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
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\BankAccountTest;
use PHPUnit\TestFixture\TestThatIsNeitherTestCaseNorPhptTestCase;
use RecursiveArrayIterator;

#[CoversClass(IncludeGroupFilterIterator::class)]
#[CoversClass(GroupFilterIterator::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/filter')]
final class IncludeGroupFilterIteratorTest extends TestCase
{
    public function testAcceptsTestsThatAreInTheSelectedGroup(): void
    {
        $this->assertSame(
            [
                BankAccountTest::class . '::testBalanceIsInitiallyZero',
                BankAccountTest::class . '::testBalanceCannotBecomeNegative2',
            ],
            $this->idsOfAcceptedTests(['one']),
        );
    }

    public function testAcceptsTestThatIsInSeveralSelectedGroupsOnlyOnce(): void
    {
        $this->assertSame(
            [
                BankAccountTest::class . '::testBalanceIsInitiallyZero',
                BankAccountTest::class . '::testBalanceCannotBecomeNegative',
                BankAccountTest::class . '::testBalanceCannotBecomeNegative2',
            ],
            $this->idsOfAcceptedTests(['one', 'two']),
        );
    }

    public function testAcceptsTestsThatAreInTheSelectedGroupWhoseNameIsANumber(): void
    {
        $this->assertSame(
            [BankAccountTest::class . '::testBalanceCannotBecomeNegative'],
            $this->idsOfAcceptedTests(['5']),
        );
    }

    public function testDoesNotAcceptTestsThatAreNotInTheSelectedGroup(): void
    {
        $this->assertSame([], $this->idsOfAcceptedTests(['group-without-tests']));
    }

    public function testAcceptsTestSuitesSoThatTheTestsTheyContainCanBeFiltered(): void
    {
        $childSuite = TestSuite::empty('child test suite name');

        $childSuite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'), ['one']);
        $childSuite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative'), ['two']);

        $suite = TestSuite::empty('test suite name');

        $suite->addTest($childSuite);

        $factory = new Factory;

        $factory->addIncludeGroupFilter(['one']);

        $suite->injectFilter($factory);

        $ids = [];

        foreach ($suite->getIterator() as $test) {
            assert($test instanceof TestSuite);

            foreach ($test as $testOfChildSuite) {
                assert($testOfChildSuite instanceof TestCase);

                $ids[] = $testOfChildSuite->valueObjectForEvents()->id();
            }
        }

        $this->assertSame([BankAccountTest::class . '::testBalanceIsInitiallyZero'], $ids);
    }

    public function testAcceptsTestThatIsNeitherTestCaseNorPhptTestCase(): void
    {
        $iterator = new IncludeGroupFilterIterator(
            new RecursiveArrayIterator([new TestThatIsNeitherTestCaseNorPhptTestCase]),
            ['one'],
            TestSuite::empty('test suite name'),
        );

        $this->assertCount(1, iterator_to_array($iterator));
    }

    /**
     * @return list<non-empty-string>
     */
    private function idsOfAcceptedTests(array $groups): array
    {
        $suite = TestSuite::empty('test suite name');

        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'), ['one']);
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative'), ['two', '5']);
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative2'), ['one', 'two']);

        $factory = new Factory;

        $factory->addIncludeGroupFilter($groups);

        $suite->injectFilter($factory);

        $ids = [];

        foreach ($suite->getIterator() as $test) {
            assert($test instanceof TestCase);

            $ids[] = $test->valueObjectForEvents()->id();
        }

        return $ids;
    }
}
