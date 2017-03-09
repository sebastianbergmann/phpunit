<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\TestCase;

class GlobalStateTest extends TestCase
{
    public function testIncludedFilesAsStringSkipsVfsProtocols()
    {
        $dir   = __DIR__;
        $files = [
            'phpunit', // The 0 index is not used
            $dir . '/ConfigurationTest.php',
            $dir . '/GlobalStateTest.php',
            'vfs://' . $dir . '/RegexTest.php',
            'phpvfs53e46260465c7://' . $dir . '/TestTest.php',
            'file://' . $dir . '/XmlTest.php'
        ];

        $this->assertEquals(
            "require_once '" . $dir . "/ConfigurationTest.php';\n" .
            "require_once '" . $dir . "/GlobalStateTest.php';\n" .
            "require_once 'file://" . $dir . "/XmlTest.php';\n",
            GlobalState::processIncludedFilesAsString($files)
        );
    }
}
