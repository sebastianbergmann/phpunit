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

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class LineNumberFilterIteratorTest extends TestCase
{
    public function testStartOfMethodMatch(): void
    {
        $this->assertCount(1, $this->createFilter(14));
    }

    public function testMiddleOfMethodMatch(): void
    {
        $this->assertCount(1, $this->createFilter(16));
    }

    public function testEndOfMethodMatch(): void
    {
        $this->assertCount(1, $this->createFilter(17));
    }

    public function testBeforeMethodDoesNotMatch(): void
    {
        $this->assertCount(0, $this->createFilter(13));
    }

    private function createFilter(int $lineNumber): LineNumberFilterIterator
    {
        $suite = new TestSuite;
        $suite->addTest(new \DummyBarTest('testBarEqualsBar'));

        $iterator = new LineNumberFilterIterator($suite->getIterator(), $lineNumber);

        $iterator->rewind();

        return $iterator;
    }
}
