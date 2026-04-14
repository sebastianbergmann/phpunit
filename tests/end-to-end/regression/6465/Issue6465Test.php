<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6465;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class Issue6465Test extends TestCase
{
    #[RunInSeparateProcess]
    public function testRequestTimeFloatIsNotInheritedFromParent(): void
    {
        $this->assertNotSame(1.0, $_SERVER['REQUEST_TIME_FLOAT']);
        $this->assertNotSame(1, $_SERVER['REQUEST_TIME']);
    }

    #[RunInSeparateProcess]
    public function testScriptFilenameIsNotInheritedFromParent(): void
    {
        $this->assertNotSame('/fake/parent/script.php', $_SERVER['SCRIPT_FILENAME']);
    }
}
