<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Success;

#[CoversClass(GlobalStateCapture::class)]
#[Small]
final class GlobalStateCaptureTest extends TestCase
{
    public function testCreateSnapshotAppliesBackupGlobalsExcludeList(): void
    {
        $GLOBALS['phpunitCaptureTestGlobal'] = 'present';

        try {
            $capture = new GlobalStateCapture;

            $capture->setBackupGlobalsExcludeList(['phpunitCaptureTestGlobal']);

            $snapshot = $capture->createSnapshot(new Success('testOne'), Facade::emitter(), true);

            $this->assertArrayNotHasKey('phpunitCaptureTestGlobal', $snapshot->globalVariables());
        } finally {
            unset($GLOBALS['phpunitCaptureTestGlobal']);
        }
    }

    public function testSnapshotGlobalsIsSkippedWhenRunningInSeparateProcess(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything())
            ->seal();

        $capture = new GlobalStateCapture;

        $capture->setBackupGlobals(true);
        $capture->snapshotGlobals(new Success('testOne'), $emitter, false, true);
        $capture->restoreGlobals(new Success('testOne'), $emitter);
    }

    public function testSnapshotGlobalsIsSkippedInIsolation(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything())
            ->seal();

        $capture = new GlobalStateCapture;
        $capture->setBackupGlobals(true);

        $capture->snapshotGlobals(new Success('testOne'), $emitter, true, null);
        $capture->restoreGlobals(new Success('testOne'), $emitter);
    }

    public function testSnapshotGlobalsIsSkippedWhenNoBackupRequested(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything())
            ->seal();

        $capture = new GlobalStateCapture;

        $capture->snapshotGlobals(new Success('testOne'), $emitter, false, null);
        $capture->restoreGlobals(new Success('testOne'), $emitter);
    }

    public function testRestoreErrorHandlersWithoutSnapshotIsClean(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method('testConsideredRisky')
            ->seal();

        $capture = new GlobalStateCapture;

        $capture->snapshotErrorHandlers(new Success('testOne'), $emitter);
        $capture->restoreErrorHandlers(new Success('testOne'), $emitter, false);
    }
}
