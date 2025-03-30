<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue2725;

use function getmypid;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[RunClassInSeparateProcess]
class BeforeAfterClassPidTest extends TestCase
{
    public const PID_VARIABLE = 'current_pid';

    #[BeforeClass]
    public static function showPidBefore(): void
    {
        $GLOBALS[static::PID_VARIABLE] = getmypid();
    }

    #[AfterClass]
    public static function showPidAfter(): void
    {
        if ($GLOBALS[static::PID_VARIABLE] - getmypid() !== 0) {
            print "\n@afterClass output - PID difference should be zero!";
        }

        unset($GLOBALS[static::PID_VARIABLE]);
    }

    public function testMethod1WithItsBeforeAndAfter(): void
    {
        $this->assertEquals($GLOBALS[static::PID_VARIABLE], getmypid());
    }

    public function testMethod2WithItsBeforeAndAfter(): void
    {
        $this->assertEquals($GLOBALS[static::PID_VARIABLE], getmypid());
    }
}
