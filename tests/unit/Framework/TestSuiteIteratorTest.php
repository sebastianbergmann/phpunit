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
    /*
     * tests for the initial state with empty test suite
     */

    public function testKeyForEmptyTestSuiteInitiallyReturnsZero(): void
    {
        $testSuite = new TestSuite();
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertSame(0, $subject->key());
    }

    public function testValidForEmptyTestSuiteInitiallyReturnsFalse(): void
    {
        $testSuite = new TestSuite();
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertFalse($subject->valid());
    }

    public function testCurrentForEmptyTestSuiteInitiallyReturnsNull(): void
    {
        $testSuite = new TestSuite();
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertNull($subject->current());
    }

    /*
     * tests for the initial state with non-empty test suite
     */

    public function testKeyForNonEmptyTestSuiteInitiallyReturnsZero(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);

        $this->assertSame(0, $subject->key());
    }

    public function testValidForNonEmptyTestSuiteInitiallyReturnsTrue(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);

        $this->assertTrue($subject->valid());
    }

    public function testCurrentForNonEmptyTestSuiteInitiallyReturnsFirstTest(): void
    {
        $test      = new \EmptyTestCaseTest();
        $testSuite = new TestSuite();
        $testSuite->addTest($test);
        $subject = new TestSuiteIterator($testSuite);

        $this->assertSame($test, $subject->current());
    }

    /*
     * tests for rewind
     */

    public function testRewindResetsKeyToZero(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);
        $subject->next();

        $subject->rewind();

        $this->assertSame(0, $subject->key());
    }

    public function testRewindResetsCurrentToFirstElement(): void
    {
        $testSuite = new TestSuite();
        $test      = new \EmptyTestCaseTest();
        $testSuite->addTest($test);
        $subject = new TestSuiteIterator($testSuite);
        $subject->next();

        $subject->rewind();

        $this->assertSame($test, $subject->current());
    }

    /*
     * tests for next
     */

    public function testNextIncreasesKeyFromZeroToOne(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);

        $subject->next();

        $this->assertSame(1, $subject->key());
    }

    public function testCurrentAfterLastElementReturnsNull(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);

        $subject->next();

        $this->assertNull($subject->current());
    }

    public function testValidAfterLastElementReturnsFalse(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);

        $subject->next();

        $this->assertFalse($subject->valid());
    }

    /*
     * tests for getChildren
     */

    public function testGetChildrenForEmptyTestSuiteThrowsException(): void
    {
        $testSuite = new TestSuite();
        $subject   = new TestSuiteIterator($testSuite);

        $this->expectException(UnexpectedValueException::class);

        $subject->getChildren();
    }

    public function testGetChildrenForCurrentTestThrowsException(): void
    {
        $testSuite = new TestSuite();
        $testSuite->addTest(new \EmptyTestCaseTest());
        $subject = new TestSuiteIterator($testSuite);

        $this->expectException(UnexpectedValueException::class);

        $subject->getChildren();
    }

    public function testGetChildrenReturnsNewInstanceWithCurrentTestSuite(): void
    {
        $childSuite = new TestSuite();
        $test       = new \EmptyTestCaseTest();
        $childSuite->addTest($test);

        $testSuite  = new TestSuite();
        $testSuite->addTest($childSuite);

        $subject = new TestSuiteIterator($testSuite);

        $children = $subject->getChildren();

        $this->assertNotSame($subject, $children);
        $this->assertSame($test, $children->current());
    }

    /*
     * tests for hasChildren
     */

    public function testHasChildrenForCurrentTestSuiteReturnsTrue(): void
    {
        $testSuite  = new TestSuite();
        $childSuite = new TestSuite();
        $testSuite->addTest($childSuite);
        $subject = new TestSuiteIterator($testSuite);

        $this->assertTrue($subject->hasChildren());
    }

    public function testHasChildrenForCurrentTestReturnsFalse(): void
    {
        $testSuite = new TestSuite();
        $test      = new \EmptyTestCaseTest();
        $testSuite->addTest($test);
        $subject = new TestSuiteIterator($testSuite);

        $this->assertFalse($subject->hasChildren());
    }

    public function testHasChildrenForNoTestsReturnsFalse(): void
    {
        $testSuite = new TestSuite();
        $subject   = new TestSuiteIterator($testSuite);

        $this->assertFalse($subject->hasChildren());
    }
}
