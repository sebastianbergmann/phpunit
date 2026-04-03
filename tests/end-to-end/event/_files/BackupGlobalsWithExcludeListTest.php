<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
#[ExcludeGlobalVariableFromBackup('excludedVar')]
final class BackupGlobalsWithExcludeListTest extends TestCase
{
    public function testOne(): void
    {
        $GLOBALS['excludedVar'] = 'should not be backed up';

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertSame('should not be backed up', $GLOBALS['excludedVar'] ?? null);

        unset($GLOBALS['excludedVar']);
    }
}
