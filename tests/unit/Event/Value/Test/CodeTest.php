<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code\IssueTrigger;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Code::class)]
#[Small]
final class CodeTest extends TestCase
{
    public function testFirstPartyIsFirstPartyOrTest(): void
    {
        $this->assertTrue(Code::FirstParty->isFirstPartyOrTest());
    }

    public function testTestIsFirstPartyOrTest(): void
    {
        $this->assertTrue(Code::Test->isFirstPartyOrTest());
    }

    public function testThirdPartyIsNotFirstPartyOrTest(): void
    {
        $this->assertFalse(Code::ThirdParty->isFirstPartyOrTest());
    }

    public function testPhpIsNotFirstPartyOrTest(): void
    {
        $this->assertFalse(Code::PHP->isFirstPartyOrTest());
    }

    public function testThirdPartyIsThirdPartyOrPhp(): void
    {
        $this->assertTrue(Code::ThirdParty->isThirdPartyOrPhpunitOrPhp());
    }

    public function testPhpIsThirdPartyOrPhp(): void
    {
        $this->assertTrue(Code::PHP->isThirdPartyOrPhpunitOrPhp());
    }

    public function testFirstPartyIsNotThirdPartyOrPhp(): void
    {
        $this->assertFalse(Code::FirstParty->isThirdPartyOrPhpunitOrPhp());
    }

    public function testTestIsNotThirdPartyOrPhp(): void
    {
        $this->assertFalse(Code::Test->isThirdPartyOrPhpunitOrPhp());
    }
}
