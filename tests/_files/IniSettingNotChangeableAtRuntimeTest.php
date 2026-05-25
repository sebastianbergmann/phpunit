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

use function ini_get;
use function ini_set;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\TestCase;

final class IniSettingNotChangeableAtRuntimeTest extends TestCase
{
    #[PreserveGlobalState(true)]
    public function testIniSettingNotChangeableAtRuntimeIsForwardedToChildProcess(): void
    {
        // max_input_time is PHP_INI_PERDIR and therefore cannot be changed
        // using ini_set() at runtime. This guarantees that the value asserted
        // below was not set by the test (or PHPUnit) inside the child process,
        // but can only have been forwarded as a command line option by PHPUnit
        // when it spawned the child process for this isolated test.
        $this->assertFalse(@ini_set('max_input_time', '1'));

        // The parent process was started with -d max_input_time=42 (see the
        // --INI-- section of the calling .phpt file). For the child process to
        // see this value, PHPUnit has to detect that the setting differs from
        // its default and forward it. This relies on the interplay with
        // SebastianBergmann\Environment\Runtime::getSettingsNotChangeableAtRuntime().
        $this->assertSame('42', ini_get('max_input_time'));
    }
}
