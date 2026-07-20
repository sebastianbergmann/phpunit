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

#[CoversClass(PhptWorkUnit::class)]
#[Small]
final class PhptWorkUnitTest extends TestCase
{
    public function testHasIndex(): void
    {
        $this->assertSame(5, $this->unit()->index());
    }

    public function testHasFile(): void
    {
        $this->assertSame('/path/to/test.phpt', $this->unit()->file());
    }

    public function testIsNamedAfterItsFile(): void
    {
        $this->assertSame('/path/to/test.phpt', $this->unit()->name());
    }

    public function testHasNoConflictsByDefault(): void
    {
        $this->assertSame([], $this->unit()->conflicts());
    }

    public function testHasConflicts(): void
    {
        $unit = new PhptWorkUnit(5, '/path/to/test.phpt', ['all']);

        $this->assertSame(['all'], $unit->conflicts());
    }

    private function unit(): PhptWorkUnit
    {
        return new PhptWorkUnit(5, '/path/to/test.phpt');
    }
}
