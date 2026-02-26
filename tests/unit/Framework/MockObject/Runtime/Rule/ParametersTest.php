<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Rule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[CoversClass(Parameters::class)]
#[Group('test-doubles')]
#[Small]
final class ParametersTest extends TestCase
{
    public function testVerifyWithoutApplyThrowsException(): void
    {
        $rule = new Parameters([new IsEqual(1)]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Doubled method does not exist.');

        $rule->verify();
    }

    #[IgnorePhpunitDeprecations]
    public function testThrowsWhenTooFewArgumentsProvided(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->method('doSomething')->with(1);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('is too low');

        $double->doSomething();
    }

    #[IgnorePhpunitDeprecations]
    public function testThrowsHintMessageWhenWithAnythingUsedAndZeroArgs(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->method('doSomething')->with($this->anything());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('withAnyParameters');

        $double->doSomething();
    }

    public function testCachedSuccessResultIsReusedOnVerify(): void
    {
        $double = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $double->expects($this->once())->method('doSomethingElse')->with(1)->willReturn(2);

        $result = $double->doSomethingElse(1);

        $this->assertSame(2, $result);
    }
}
