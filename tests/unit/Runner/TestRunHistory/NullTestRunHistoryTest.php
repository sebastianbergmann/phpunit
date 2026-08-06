<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;

#[CoversClass(NullTestRunHistory::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-run-history')]
final class NullTestRunHistoryTest extends TestCase
{
    public function testSetStatusIsNoOp(): void
    {
        $cache = new NullTestRunHistory;
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setStatus($id, TestStatus::failure('failure'));

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testRemoveIsNoOp(): void
    {
        $cache = new NullTestRunHistory;
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->remove($id);

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testStatusReturnsUnknown(): void
    {
        $cache = new NullTestRunHistory;
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testSetTimeIsNoOp(): void
    {
        $cache = new NullTestRunHistory;
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setTime($id, 1.234);

        $this->assertSame(0.0, $cache->time($id));
    }

    public function testTimeReturnsZero(): void
    {
        $cache = new NullTestRunHistory;
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertSame(0.0, $cache->time($id));
    }

    public function testLoadIsNoOp(): void
    {
        $cache = new NullTestRunHistory;

        $cache->load();

        $this->assertTrue($cache->status(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne'))->isUnknown());
    }

    public function testPersistIsNoOp(): void
    {
        $cache = new NullTestRunHistory;

        $cache->persist();

        $this->assertTrue($cache->status(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne'))->isUnknown());
    }

    public function testPersistAndPruneIsNoOp(): void
    {
        $cache = new NullTestRunHistory;

        $cache->persistAndPrune();

        $this->assertTrue($cache->status(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne'))->isUnknown());
    }
}
