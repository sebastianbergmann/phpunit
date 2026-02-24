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

#[CoversClass(IssueTrigger::class)]
#[Small]
#[TestDox('IssueTrigger')]
final class IssueTriggerTest extends TestCase
{
    #[TestDox('(null, null) is classified as "unknown"')]
    public function testCalleeIsUnknownAndCallerIsUnknown(): void
    {
        $issueTrigger = IssueTrigger::from(null, null);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('unknown', $issueTrigger->callerAsString());
        $this->assertSame('unknown', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(null, Code::Test) is classified as "unknown"')]
    public function testCalleeIsUnknownAndCallerIsTest(): void
    {
        $issueTrigger = IssueTrigger::from(null, Code::Test);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('test code', $issueTrigger->callerAsString());
        $this->assertSame('unknown', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(null, Code::FirstParty) is classified as "unknown"')]
    public function testCalleeIsUnknownAndCallerIsFirstParty(): void
    {
        $issueTrigger = IssueTrigger::from(null, Code::FirstParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('first-party code', $issueTrigger->callerAsString());
        $this->assertSame('unknown', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(null, Code::ThirdParty) is classified as "unknown"')]
    public function testCalleeIsUnknownAndCallerIsThirdParty(): void
    {
        $issueTrigger = IssueTrigger::from(null, Code::ThirdParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('third-party code', $issueTrigger->callerAsString());
        $this->assertSame('unknown', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(null, Code::PHPUnit) is classified as "unknown"')]
    public function testCalleeIsUnknownAndCallerIsPHPUnit(): void
    {
        $issueTrigger = IssueTrigger::from(null, Code::PHPUnit);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('PHPUnit', $issueTrigger->callerAsString());
        $this->assertSame('unknown', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(null, Code::PHP) is classified as "unknown"')]
    public function testCalleeIsUnknownAndCallerIsPHP(): void
    {
        $issueTrigger = IssueTrigger::from(null, Code::PHP);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('PHP runtime', $issueTrigger->callerAsString());
        $this->assertSame('unknown', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::Test, null) is classified as "self"')]
    public function testCalleeIsTestAndCallerIsUnknown(): void
    {
        $issueTrigger = IssueTrigger::from(Code::Test, null);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('unknown', $issueTrigger->callerAsString());
        $this->assertSame('test code', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::Test, Code::Test) is classified as "self"')]
    public function testCalleeIsTestAndCallerIsTest(): void
    {
        $issueTrigger = IssueTrigger::from(Code::Test, Code::Test);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('test code', $issueTrigger->callerAsString());
        $this->assertSame('test code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by test code calling into test code', $issueTrigger->asString());
    }

    #[TestDox('(Code::Test, Code::FirstParty) is classified as "self"')]
    public function testCalleeIsTestAndCallerIsFirstParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::Test, Code::FirstParty);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('first-party code', $issueTrigger->callerAsString());
        $this->assertSame('test code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by first-party code calling into test code', $issueTrigger->asString());
    }

    #[TestDox('(Code::Test, Code::ThirdParty) is classified as "self"')]
    public function testCalleeIsTestAndCallerIsThirdParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::Test, Code::ThirdParty);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('third-party code', $issueTrigger->callerAsString());
        $this->assertSame('test code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by third-party code calling into test code', $issueTrigger->asString());
    }

    #[TestDox('(Code::Test, Code::PHPUnit) is classified as "self"')]
    public function testCalleeIsTestAndCallerIsPHPUnit(): void
    {
        $issueTrigger = IssueTrigger::from(Code::Test, Code::PHPUnit);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHPUnit', $issueTrigger->callerAsString());
        $this->assertSame('test code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHPUnit calling into test code', $issueTrigger->asString());
    }

    #[TestDox('(Code::Test, Code::PHP) is classified as "self"')]
    public function testCalleeIsTestAndCallerIsPHP(): void
    {
        $issueTrigger = IssueTrigger::from(Code::Test, Code::PHP);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHP runtime', $issueTrigger->callerAsString());
        $this->assertSame('test code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHP runtime calling into test code', $issueTrigger->asString());
    }

    #[TestDox('(Code::FirstParty, null) is classified as "self"')]
    public function testCalleeIsFirstPartyAndCallerIsUnknown(): void
    {
        $issueTrigger = IssueTrigger::from(Code::FirstParty, null);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('unknown', $issueTrigger->callerAsString());
        $this->assertSame('first-party code', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::FirstParty, Code::Test) is classified as "self"')]
    public function testCalleeIsFirstPartyAndCallerIsTest(): void
    {
        $issueTrigger = IssueTrigger::from(Code::FirstParty, Code::Test);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('test code', $issueTrigger->callerAsString());
        $this->assertSame('first-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by test code calling into first-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::FirstParty, Code::FirstParty) is classified as "self"')]
    public function testCalleeIsFirstPartyAndCallerIsFirstParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::FirstParty, Code::FirstParty);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('first-party code', $issueTrigger->callerAsString());
        $this->assertSame('first-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by first-party code calling into first-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::FirstParty, Code::ThirdParty) is classified as "self"')]
    public function testCalleeIsFirstPartyAndCallerIsThirdParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::FirstParty, Code::ThirdParty);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('third-party code', $issueTrigger->callerAsString());
        $this->assertSame('first-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by third-party code calling into first-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::FirstParty, Code::PHPUnit) is classified as "self"')]
    public function testCalleeIsFirstPartyAndCallerIsPHPUnit(): void
    {
        $issueTrigger = IssueTrigger::from(Code::FirstParty, Code::PHPUnit);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHPUnit', $issueTrigger->callerAsString());
        $this->assertSame('first-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHPUnit calling into first-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::FirstParty, Code::PHP) is classified as "self"')]
    public function testCalleeIsFirstPartyAndCallerIsPHP(): void
    {
        $issueTrigger = IssueTrigger::from(Code::FirstParty, Code::PHP);

        $this->assertTrue($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHP runtime', $issueTrigger->callerAsString());
        $this->assertSame('first-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHP runtime calling into first-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::ThirdParty, null) is classified as "unknown"')]
    public function testCalleeIsThirdPartyAndCallerIsUnknown(): void
    {
        $issueTrigger = IssueTrigger::from(Code::ThirdParty, null);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('unknown', $issueTrigger->callerAsString());
        $this->assertSame('third-party code', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::ThirdParty, Code::Test) is classified as "direct"')]
    public function testCalleeIsThirdPartyAndCallerIsTest(): void
    {
        $issueTrigger = IssueTrigger::from(Code::ThirdParty, Code::Test);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertTrue($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('test code', $issueTrigger->callerAsString());
        $this->assertSame('third-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by test code calling into third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::ThirdParty, Code::FirstParty) is classified as "direct"')]
    public function testCalleeIsThirdPartyAndCallerIsFirstParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::ThirdParty, Code::FirstParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertTrue($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('first-party code', $issueTrigger->callerAsString());
        $this->assertSame('third-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by first-party code calling into third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::ThirdParty, Code::ThirdParty) is classified as "indirect"')]
    public function testCalleeIsThirdPartyAndCallerIsThirdParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::ThirdParty, Code::ThirdParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('third-party code', $issueTrigger->callerAsString());
        $this->assertSame('third-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by third-party code calling into third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::ThirdParty, Code::PHPUnit) is classified as "indirect"')]
    public function testCalleeIsThirdPartyAndCallerIsPHPUnit(): void
    {
        $issueTrigger = IssueTrigger::from(Code::ThirdParty, Code::PHPUnit);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHPUnit', $issueTrigger->callerAsString());
        $this->assertSame('third-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHPUnit calling into third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::ThirdParty, Code::PHP) is classified as "indirect"')]
    public function testCalleeIsThirdPartyAndCallerIsPHP(): void
    {
        $issueTrigger = IssueTrigger::from(Code::ThirdParty, Code::PHP);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHP runtime', $issueTrigger->callerAsString());
        $this->assertSame('third-party code', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHP runtime calling into third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHPUnit, null) is classified as "unknown"')]
    public function testCalleeIsPHPUnitAndCallerIsUnknown(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHPUnit, null);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('unknown', $issueTrigger->callerAsString());
        $this->assertSame('PHPUnit', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHPUnit, Code::Test) is classified as "direct"')]
    public function testCalleeIsPHPUnitAndCallerIsTest(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHPUnit, Code::Test);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertTrue($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('test code', $issueTrigger->callerAsString());
        $this->assertSame('PHPUnit', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by test code calling into PHPUnit', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHPUnit, Code::FirstParty) is classified as "direct"')]
    public function testCalleeIsPHPUnitAndCallerIsFirstParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHPUnit, Code::FirstParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertTrue($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('first-party code', $issueTrigger->callerAsString());
        $this->assertSame('PHPUnit', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by first-party code calling into PHPUnit', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHPUnit, Code::ThirdParty) is classified as "indirect"')]
    public function testCalleeIsPHPUnitAndCallerIsThirdParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHPUnit, Code::ThirdParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('third-party code', $issueTrigger->callerAsString());
        $this->assertSame('PHPUnit', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by third-party code calling into PHPUnit', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHPUnit, Code::PHPUnit) is classified as "indirect"')]
    public function testCalleeIsPHPUnitAndCallerIsPHPUnit(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHPUnit, Code::PHPUnit);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHPUnit', $issueTrigger->callerAsString());
        $this->assertSame('PHPUnit', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHPUnit calling into PHPUnit', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHPUnit, Code::PHP) is classified as "indirect"')]
    public function testCalleeIsPHPUnitAndCallerIsPHP(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHPUnit, Code::PHP);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHP runtime', $issueTrigger->callerAsString());
        $this->assertSame('PHPUnit', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHP runtime calling into PHPUnit', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHP, null) is classified as "unknown"')]
    public function testCalleeIsPHPAndCallerIsUnknown(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHP, null);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertTrue($issueTrigger->isUnknown());
        $this->assertSame('unknown', $issueTrigger->callerAsString());
        $this->assertSame('PHP runtime', $issueTrigger->calleeAsString());
        $this->assertSame('unknown if issue was triggered in first-party code or third-party code', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHP, Code::Test) is classified as "direct"')]
    public function testCalleeIsPHPAndCallerIsTest(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHP, Code::Test);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertTrue($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('test code', $issueTrigger->callerAsString());
        $this->assertSame('PHP runtime', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by test code calling into PHP runtime', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHP, Code::FirstParty) is classified as "direct"')]
    public function testCalleeIsPHPAndCallerIsFirstParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHP, Code::FirstParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertTrue($issueTrigger->isDirect());
        $this->assertFalse($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('first-party code', $issueTrigger->callerAsString());
        $this->assertSame('PHP runtime', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by first-party code calling into PHP runtime', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHP, Code::ThirdParty) is classified as "indirect"')]
    public function testCalleeIsPHPAndCallerIsThirdParty(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHP, Code::ThirdParty);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('third-party code', $issueTrigger->callerAsString());
        $this->assertSame('PHP runtime', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by third-party code calling into PHP runtime', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHP, Code::PHPUnit) is classified as "indirect"')]
    public function testCalleeIsPHPAndCallerIsPHPUnit(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHP, Code::PHPUnit);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHPUnit', $issueTrigger->callerAsString());
        $this->assertSame('PHP runtime', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHPUnit calling into PHP runtime', $issueTrigger->asString());
    }

    #[TestDox('(Code::PHP, Code::PHP) is classified as "indirect"')]
    public function testCalleeIsPHPAndCallerIsPHP(): void
    {
        $issueTrigger = IssueTrigger::from(Code::PHP, Code::PHP);

        $this->assertFalse($issueTrigger->isSelf());
        $this->assertFalse($issueTrigger->isDirect());
        $this->assertTrue($issueTrigger->isIndirect());
        $this->assertFalse($issueTrigger->isUnknown());
        $this->assertSame('PHP runtime', $issueTrigger->callerAsString());
        $this->assertSame('PHP runtime', $issueTrigger->calleeAsString());
        $this->assertSame('issue triggered by PHP runtime calling into PHP runtime', $issueTrigger->asString());
    }
}
