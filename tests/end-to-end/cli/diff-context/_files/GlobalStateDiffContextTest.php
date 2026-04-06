<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DiffContext;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
final class GlobalStateDiffContextTest extends TestCase
{
    public function testModifiesGlobalState(): void
    {
        $GLOBALS['diffContextFixture']['key08'] = 'CHANGED';

        $this->assertTrue(true);
    }
}
