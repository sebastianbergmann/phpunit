<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
#[ExcludeGlobalVariableFromBackup('foo')]
final class BackupGlobalsTest extends TestCase
{
    #[BackupGlobals(false)]
    #[ExcludeGlobalVariableFromBackup('bar')]
    public function testOne(): void
    {
    }
}
