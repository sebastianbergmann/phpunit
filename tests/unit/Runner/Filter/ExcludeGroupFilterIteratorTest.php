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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\BankAccountTest;

#[CoversClass(ExcludeGroupFilterIterator::class)]
#[CoversClass(GroupFilterIterator::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/filter')]
final class ExcludeGroupFilterIteratorTest extends TestCase
{
    public function testDoesNotAcceptTestsThatAreInTheExcludedGroup(): void
    {
        $this->assertSame(
            [BankAccountTest::class . '::testBalanceCannotBecomeNegative'],
            $this->idsOfAcceptedTests(['one']),
        );
    }

    public function testDoesNotAcceptTestThatIsInOneOfTheExcludedGroups(): void
    {
        $this->assertSame([], $this->idsOfAcceptedTests(['one', 'two']));
    }

    public function testDoesNotAcceptTestsThatAreInTheExcludedGroupWhoseNameIsANumber(): void
    {
        $this->assertSame(
            [
                BankAccountTest::class . '::testBalanceIsInitiallyZero',
                BankAccountTest::class . '::testBalanceCannotBecomeNegative2',
            ],
            $this->idsOfAcceptedTests(['5']),
        );
    }

    public function testAcceptsAllTestsWhenNoTestIsInTheExcludedGroup(): void
    {
        $this->assertSame(
            [
                BankAccountTest::class . '::testBalanceIsInitiallyZero',
                BankAccountTest::class . '::testBalanceCannotBecomeNegative',
                BankAccountTest::class . '::testBalanceCannotBecomeNegative2',
            ],
            $this->idsOfAcceptedTests(['group-without-tests']),
        );
    }

    /**
     * @param list<non-empty-string> $groups
     *
     * @return list<non-empty-string>
     */
    private function idsOfAcceptedTests(array $groups): array
    {
        $suite = TestSuite::empty('test suite name');

        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'), ['one']);
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative'), ['two', '5']);
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative2'), ['one', 'two']);

        $factory = new Factory;

        $factory->addExcludeGroupFilter($groups);

        $suite->injectFilter($factory);

        $ids = [];

        foreach ($suite->getIterator() as $test) {
            assert($test instanceof TestCase);

            $ids[] = $test->valueObjectForEvents()->id();
        }

        return $ids;
    }
}
