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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class ChunkIteratorTest extends TestCase
{
    public function testChunkOne(): void
    {
        $tests = $this->getTestsChunk(1, 2);

        $this->assertTrue(in_array(TestCase1Test::class.'::testTest', $tests));
        $this->assertTrue(in_array(TestCase2Test::class.'::testTest', $tests));
        $this->assertTrue(in_array(TestCase3Test::class.'::testTest', $tests));

        $this->assertFalse(in_array(TestCase4Test::class.'::testTest', $tests));
        $this->assertFalse(in_array(TestCase5Test::class.'::testTest', $tests));
    }

    public function testChunkTwo(): void
    {
        $tests = $this->getTestsChunk(2, 2);

        $this->assertFalse(in_array(TestCase1Test::class.'::testTest', $tests));
        $this->assertFalse(in_array(TestCase2Test::class.'::testTest', $tests));
        $this->assertFalse(in_array(TestCase3Test::class.'::testTest', $tests));

        $this->assertTrue(in_array(TestCase4Test::class.'::testTest', $tests));
        $this->assertTrue(in_array(TestCase5Test::class.'::testTest', $tests));
    }

    public function testInvalidChunk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You only configured 2 chunks but passed chunk 3');

        $tests = $this->getTestsChunk(3, 2);
    }

    /**
     * @return TestCase[]
     */
    private function getTestsChunk(int $chunk, int $numChunks): array
    {
        $suite = new TestSuite;
        $suite->addTest(new TestCase1Test('testTest'));
        $suite->addTest(new TestCase2Test('testTest'));
        $suite->addTest(new TestCase3Test('testTest'));
        $suite->addTest(new TestCase4Test('testTest'));
        $suite->addTest(new TestCase5Test('testTest'));

        $iterator = new ChunkIterator($suite->getIterator(), [
            'chunk' => $chunk,
            'numChunks' => $numChunks,
        ]);

        $iterator->rewind();

        $tests = [];

        foreach ($iterator as $test) {
            $tests[] = get_class($test).'::'.$test->getName();
        }

        return $tests;
    }
}

class TestCase1Test extends TestCase
{
    public function testTest() : void
    {
    }
}

class TestCase2Test extends TestCase
{
    public function testTest() : void
    {
    }
}

class TestCase3Test extends TestCase
{
    public function testTest() : void
    {
    }
}

class TestCase4Test extends TestCase
{
    public function testTest() : void
    {
    }
}

class TestCase5Test extends TestCase
{
    public function testTest() : void
    {
    }
}
