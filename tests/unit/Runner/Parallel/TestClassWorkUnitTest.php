<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(TestClassWorkUnit::class)]
#[Small]
final class TestClassWorkUnitTest extends TestCase
{
    public function testHasIndex(): void
    {
        $this->assertSame(3, $this->unit()->index());
    }

    public function testHasClassName(): void
    {
        $this->assertSame(WorkerFirstTest::class, $this->unit()->className());
    }

    public function testHasTests(): void
    {
        $tests = $this->unit()->tests();

        $this->assertCount(1, $tests);
        $this->assertInstanceOf(WorkerFirstTest::class, $tests[0]);
    }

    public function testIsNamedAfterItsClass(): void
    {
        $this->assertSame(WorkerFirstTest::class, $this->unit()->name());
    }

    private function unit(): TestClassWorkUnit
    {
        return new TestClassWorkUnit(
            3,
            WorkerFirstTest::class,
            [new WorkerFirstTest('testStartsTheProcessLocalCounter')],
        );
    }
}
