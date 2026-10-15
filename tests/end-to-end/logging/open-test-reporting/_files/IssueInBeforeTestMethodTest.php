<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\OpenTestReporting;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class IssueInBeforeTestMethodTest extends TestCase
{
    protected function setUp(): void
    {
        trigger_error('message', E_USER_DEPRECATED);

        if ($this->name() === 'testTwo') {
            $this->markTestSkipped('message');
        }
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
