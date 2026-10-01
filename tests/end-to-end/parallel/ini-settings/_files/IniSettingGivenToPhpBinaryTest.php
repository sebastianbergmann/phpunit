<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelIniSettings;

use function ini_get;
use PHPUnit\Framework\TestCase;

final class IniSettingGivenToPhpBinaryTest extends TestCase
{
    public function testRunsWithTheIniSettingThatWasGivenToThePhpBinary(): void
    {
        $this->assertSame('5', ini_get('precision'));
    }
}
