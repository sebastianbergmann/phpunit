<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Rule\MethodName;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\InvocationOrderThatNeverMatches;

#[CoversClass(Matcher::class)]
#[Group('test-doubles')]
#[Small]
final class MatcherTest extends TestCase
{
    public function testMethodNameRuleCannotBeQueriedWhenItHasNotBeenConfigured(): void
    {
        $matcher = new Matcher(new AnyInvokedCount, AnInterface::class);

        $this->expectException(MethodNameNotConfiguredException::class);

        $matcher->methodNameRule();
    }

    public function testCannotBeInvokedWhenMethodNameRuleHasNotBeenConfigured(): void
    {
        $matcher = new Matcher(new AnyInvokedCount, AnInterface::class);

        $this->expectException(MethodNameNotConfiguredException::class);

        $matcher->invoked($this->invocation());
    }

    public function testCannotBeInvokedWhenMatchBuilderCannotBeFound(): void
    {
        $matcher = new Matcher(new AnyInvokedCount, AnInterface::class);

        $matcher->setMethodNameRule(new MethodName('doSomething'));
        $matcher->setAfterMatchBuilderId('does-not-exist');

        $this->expectException(MatchBuilderNotFoundException::class);

        $matcher->invoked($this->invocation());
    }

    public function testCannotDetermineWhetherItMatchesInvocationWhenMethodNameRuleHasNotBeenConfigured(): void
    {
        $matcher = new Matcher(new AnyInvokedCount, AnInterface::class);

        $this->expectException(MethodNameNotConfiguredException::class);

        $matcher->matches($this->invocation());
    }

    public function testDoesNotMatchInvocationWhenInvocationRuleDoesNotMatchIt(): void
    {
        $matcher = new Matcher(new InvocationOrderThatNeverMatches, AnInterface::class);

        $matcher->setMethodNameRule(new MethodName('doSomething'));

        $this->assertFalse($matcher->matches($this->invocation()));
    }

    public function testCannotDetermineWhetherItMatchesInvocationWhenMethodNameRuleFails(): void
    {
        $matcher = new Matcher(new AnyInvokedCount, AnInterface::class);

        $matcher->setMethodNameRule(
            new MethodName(
                new Callback(
                    static function (string $methodName): bool
                    {
                        throw new ExpectationFailedException('constraint failed');
                    },
                ),
            ),
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIsOrContains('Expectation for method name is accepted by specified callback failed.');

        $matcher->matches($this->invocation());
    }

    public function testCannotBeVerifiedWhenMethodNameRuleHasNotBeenConfigured(): void
    {
        $matcher = new Matcher(new AnyInvokedCount, AnInterface::class);

        $this->expectException(MethodNameNotConfiguredException::class);

        $matcher->verify();
    }

    private function invocation(): Invocation
    {
        $double = $this->createStub(AnInterface::class);

        assert($double instanceof StubInternal);

        return new Invocation(
            AnInterface::class,
            'doSomething',
            [],
            'bool',
            $double,
        );
    }
}
