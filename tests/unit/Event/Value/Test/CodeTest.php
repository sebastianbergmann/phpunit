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
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Code::class)]
#[Small]
final class CodeTest extends TestCase
{
    #[TestDox('Code::FirstParty is first-party code or test code')]
    public function testFirstPartyIsFirstPartyOrTest(): void
    {
        $this->assertTrue(Code::FirstParty->isFirstPartyOrTest());
        $this->assertFalse(Code::FirstParty->isThirdPartyOrPhpunitOrPhp());
    }

    #[TestDox('Code::Test is first-party code or test code')]
    public function testTestIsFirstPartyOrTest(): void
    {
        $this->assertTrue(Code::Test->isFirstPartyOrTest());
        $this->assertFalse(Code::Test->isThirdPartyOrPhpunitOrPhp());
    }

    #[TestDox('Code::ThirdParty is third-party code or PHPUnit or PHP runtime')]
    public function testThirdPartyIsThirdPartyOrPhpunitOrPhp(): void
    {
        $this->assertTrue(Code::ThirdParty->isThirdPartyOrPhpunitOrPhp());
        $this->assertFalse(Code::ThirdParty->isFirstPartyOrTest());
    }

    #[TestDox('Code::PHPUnit is third-party code or PHPUnit or PHP runtime')]
    public function testPhpunitIsThirdPartyOrPhpunitOrPhp(): void
    {
        $this->assertTrue(Code::PHPUnit->isThirdPartyOrPhpunitOrPhp());
        $this->assertFalse(Code::PHPUnit->isFirstPartyOrTest());
    }

    #[TestDox('Code::PHP is third-party code or PHPUnit or PHP runtime')]
    public function testPhpIsThirdPartyOrPhpunitOrPhp(): void
    {
        $this->assertTrue(Code::PHP->isThirdPartyOrPhpunitOrPhp());
        $this->assertFalse(Code::PHP->isFirstPartyOrTest());
    }
}
