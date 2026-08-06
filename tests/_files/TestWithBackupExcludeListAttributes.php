<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestBuilder;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\Attributes\ExcludeStaticPropertyFromBackup;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
#[BackupStaticProperties(true)]
#[ExcludeGlobalVariableFromBackup('variable')]
#[ExcludeStaticPropertyFromBackup(TestWithBackupExcludeListAttributes::class, 'firstProperty')]
#[ExcludeStaticPropertyFromBackup(TestWithBackupExcludeListAttributes::class, 'secondProperty')]
final class TestWithBackupExcludeListAttributes extends TestCase
{
    public static mixed $firstProperty  = null;
    public static mixed $secondProperty = null;

    public function testOne(): void
    {
    }
}
