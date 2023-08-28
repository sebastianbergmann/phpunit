<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace unit\Util;

use ArrayIterator;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TestFixture\Success;
use PHPUnit\Util\TestSuiteIteratorIterator;
use RuntimeException;
use Traversable;

#[CoversClass(TestSuiteIteratorIterator::class)]
#[Small]
final class TestSuiteIteratorIteratorTest extends TestCase
{
    public function testCurrentInitiallyReturnsFirstTestThatPassesFilter(): void
    {
        $suite   = $this->suiteWithFilter(false);
        $subject = new TestSuiteIteratorIterator($suite);

        $subject->rewind();
        $test = $subject->current();

        $this->assertInstanceOf(TestCase::class, $test);

        /** @var TestCase $test */
        $this->assertEquals('testTwo', $test->name());
    }

    public function testCurrentInitiallyReturnsFirstTestThatPassesFilterOnSub(): void
    {
        $suite   = $this->suiteWithFilter(true);
        $subject = new TestSuiteIteratorIterator($suite);

        $subject->rewind();
        $test = $subject->current();

        $this->assertInstanceOf(TestCase::class, $test);

        /** @var TestCase $test */
        $this->assertEquals('testTwo', $test->name());
    }

    public function testCallGetChildrenWhileNotOnSuiteGivesNoException(): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTest(new Success('testOne'));
        $subject = new TestSuiteIteratorIterator($suite);

        $this->assertNull($subject->callGetChildren());
    }

    public function testNonRecursiveIteratorGivesNoException(): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTest($this->getNonRecursiveIteratorAggregateTest());
        $subject = new TestSuiteIteratorIterator($suite);

        $this->assertNull($subject->callGetChildren());
    }

    private function suiteWithFilter(bool $filterOnSub): TestSuite
    {
        $suite = TestSuite::empty('test suite name');
        $sub   = TestSuite::empty('child test suite name');

        $sub->addTest(new Success('testOne'));
        $sub->addTest(new Success('testTwo'));

        $suite->addTest($sub);

        $factory = new Factory;
        $factory->addNameFilter('Two');

        if ($filterOnSub) {
            $sub->injectFilter($factory);
        } else {
            $suite->injectFilter($factory);
        }

        return $suite;
    }

    private function getNonRecursiveIteratorAggregateTest(): Test
    {
        return new class implements IteratorAggregate, Test
        {
            public function getIterator(): Traversable
            {
                return new ArrayIterator([]);
            }

            public function count(): int
            {
                return 0;
            }

            public function run(): void
            {
                throw new RuntimeException;
            }
        };
    }
}
