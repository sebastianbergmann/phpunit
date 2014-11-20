<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This is just a test which delays executions.
 *
 * @package    PHPUnit
 * @author     Henrique Moody <henriquemoody@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */
class SleepTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group specification
     */
    public function testDoNotSleep()
    {
        $this->assertSame(0, sleep(0));
    }

    /**
     * @group specification
     */
    public function testSleepByTenSeconds()
    {
        $this->assertSame(0, sleep(10));
    }
}
