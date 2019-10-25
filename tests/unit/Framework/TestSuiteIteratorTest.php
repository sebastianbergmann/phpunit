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

/**
 * @small
 */
final class TestSuiteIteratorTest extends TestCase
{
    public function testKeyForEmptyTestSuiteInitiallyReturnsZero(): void
    {
        $testSuite = new TestSuite;
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertSame(0, $subject->key());
    }

    public function testValidForEmptyTestSuiteInitiallyReturnsFalse(): void
    {
        $testSuite = new TestSuite;
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
        $test      = new \EmptyTestCaseTest;
        $testSuite = new TestSuite;
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
        $testSuite = new TestSuite;
        $test      = new \EmptyTestCaseTest;
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
        $subject   = new TestSuiteIterator(new TestSuite);

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
        $childSuite = new TestSuite;
        $test       = new \EmptyTestCaseTest;
        $childSuite->addTest($test);

        $testSuite  = new TestSuite;
        $testSuite->addTest($childSuite);

        $subject = new TestSuiteIterator($testSuite);

        $children = $subject->getChildren();

        $this->assertNotSame($subject, $children);
        $this->assertSame($test, $children->current());
    }

    public function testHasChildrenForCurrentTestSuiteReturnsTrue(): void
    {
        $testSuite  = new TestSuite;
        $childSuite = new TestSuite;
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
        $subject = new TestSuiteIterator(new TestSuite);

        $this->assertFalse($subject->hasChildren());
    }

    private function suiteWithEmptyTestCase(): TestSuite
    {
        $suite = new TestSuite;

        $suite->addTest(new \EmptyTestCaseTest);

        return $suite;
    }
}
