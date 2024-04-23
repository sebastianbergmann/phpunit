<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5822;

use PHPUnit\Framework\TestCase;

class Issue5822Test extends TestCase
{
    public function testDebugBacktrace()
    {
        $this->callUserFuncExample();
        $this->assertTrue(true);
    }

    private function callUserFuncExample(): void
    {
        call_user_func([$this, 'exampleCallback']);
    }

    private function exampleCallback(): void
    {
        trigger_error('My Deprecation Error', \E_USER_DEPRECATED);
    }
}
