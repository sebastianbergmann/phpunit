<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\Success;

#[CoversClass(TestSuiteIterator::class)]
#[Small]
final class TestSuiteIteratorTest extends TestCase
{
    public function testKeyForEmptyTestSuiteInitiallyReturnsZero(): void
    {
        $testSuite = TestSuite::empty('test suite name');
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertSame(0, $subject->key());
    }

    public function testValidForEmptyTestSuiteInitiallyReturnsFalse(): void
    {
        $testSuite = TestSuite::empty('test suite name');
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertFalse($subject->valid());
    }

    public function testKeyForNonEmptyTestSuiteInitiallyReturnsZero(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $this->assertSame(0, $subject->key());
    }

    public function testValidForNonEmptyTestSuiteInitiallyReturnsTrue(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $this->assertTrue($subject->valid());
    }

    public function testCurrentForNonEmptyTestSuiteInitiallyReturnsFirstTest(): void
    {
        $test      = new Success('testOne');
        $testSuite = TestSuite::empty('test suite name');
        $testSuite->addTest($test);
        $subject = new TestSuiteIterator($testSuite);

        $this->assertSame($test, $subject->current());
    }

    public function testRewindResetsKeyToZero(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $subject->next();
        $subject->rewind();

        $this->assertSame(0, $subject->key());
    }

    public function testRewindResetsCurrentToFirstElement(): void
    {
        $testSuite = TestSuite::empty('test suite name');
        $test      = new Success('testOne');
        $testSuite->addTest($test);
        $subject = new TestSuiteIterator($testSuite);
        $subject->next();

        $subject->rewind();

        $this->assertSame($test, $subject->current());
    }

    public function testNextIncreasesKeyFromZeroToOne(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $subject->next();

        $this->assertSame(1, $subject->key());
    }

    public function testValidAfterLastElementReturnsFalse(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $subject->next();

        $this->assertFalse($subject->valid());
    }

    public function testGetChildrenForEmptyTestSuiteThrowsException(): void
    {
        $subject = new TestSuiteIterator(TestSuite::empty('test suite name'));

        $this->expectException(NoChildTestSuiteException::class);

        $subject->getChildren();
    }

    public function testGetChildrenForCurrentTestThrowsException(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $this->expectException(NoChildTestSuiteException::class);

        $subject->getChildren();
    }

    public function testGetChildrenReturnsNewInstanceWithCurrentTestSuite(): void
    {
        $childSuite = TestSuite::empty('test suite name');
        $test       = new Success('testOne');
        $childSuite->addTest($test);

        $testSuite = TestSuite::empty('test suite name');
        $testSuite->addTest($childSuite);

        $subject = new TestSuiteIterator($testSuite);

        $children = $subject->getChildren();

        $this->assertNotSame($subject, $children);
        $this->assertSame($test, $children->current());
    }

    public function testHasChildrenForCurrentTestSuiteReturnsTrue(): void
    {
        $testSuite  = TestSuite::empty('test suite name');
        $childSuite = TestSuite::empty('test suite name');
        $testSuite->addTest($childSuite);
        $subject = new TestSuiteIterator($testSuite);

        $this->assertTrue($subject->hasChildren());
    }

    public function testHasChildrenForCurrentTestReturnsFalse(): void
    {
        $subject = new TestSuiteIterator($this->suiteWithEmptyTestCase());

        $this->assertFalse($subject->hasChildren());
    }

    public function testHasChildrenForNoTestsReturnsFalse(): void
    {
        $subject = new TestSuiteIterator(TestSuite::empty('test suite name'));

        $this->assertFalse($subject->hasChildren());
    }

    private function suiteWithEmptyTestCase(): TestSuite
    {
        $suite = TestSuite::empty('test suite name');

        $suite->addTest(new Success('testOne'));

        return $suite;
    }
}
