<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function chdir;
use function sys_get_temp_dir;
use PHPUnit\Framework\TestCase;

final class CwdRestoreTest extends TestCase
{
    public function testChangesCwd(): void
    {
        chdir(sys_get_temp_dir());

        $this->assertTrue(true);
    }
}
