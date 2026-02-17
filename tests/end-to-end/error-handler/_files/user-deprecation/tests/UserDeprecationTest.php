<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler\UserDeprecation;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class UserDeprecationTest extends TestCase
{
    public function testFromTestCode(): void
    {
        @trigger_error('deprecation in test code', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testSelf(): void
    {
        (new FirstPartyClass)->triggerSelf();

        $this->assertTrue(true);
    }

    public function testDirect(): void
    {
        (new FirstPartyClass)->callThirdParty();

        $this->assertTrue(true);
    }

    public function testThirdPartyCallsFirstParty(): void
    {
        (new ThirdPartyClass)->callFirstParty();

        $this->assertTrue(true);
    }

    public function testIndirect(): void
    {
        (new FirstPartyClass)->callA();

        $this->assertTrue(true);
    }
}
