<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use Exception;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\HookMethod;
use PHPUnit\Runner\HookMethodCollection;
use PHPUnit\TestFixture\HookFixture;

#[CoversClass(HookMethodInvoker::class)]
#[Small]
final class HookMethodInvokerTest extends TestCase
{
    public function testHookMethodsDeclaredOnBaseTestCaseAreFiltered(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        HookMethodInvoker::invokeBeforeTest(
            new HookFixture('testOne'),
            $this->hookMethods(HookMethodCollection::defaultBefore()),
            $emitter,
        );
    }

    public function testEmitsCalledAndFinishedForSuccessfulHook(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodCalled');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodFinished')
            ->seal();

        HookMethodInvoker::invokeBeforeTest(
            new HookFixture('testOne'),
            $this->hookMethods(
                HookMethodCollection::defaultBefore()->add(new HookMethod('successfulHook', 100)),
            ),
            $emitter,
        );
    }

    public function testEmitsCalledErroredAndFinishedAndRethrowsForUnexpectedException(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodCalled');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodErrored');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodFinished')
            ->seal();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('hook errored');

        HookMethodInvoker::invokeBeforeTest(
            new HookFixture('testOne'),
            $this->hookMethods(
                HookMethodCollection::defaultBefore()->add(new HookMethod('throwingHook', 100)),
            ),
            $emitter,
        );
    }

    public function testEmitsCalledFailedAndFinishedAndRethrowsForAssertionFailedError(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodCalled');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodFailed');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodFinished')
            ->seal();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('hook failed');

        HookMethodInvoker::invokeBeforeTest(
            new HookFixture('testOne'),
            $this->hookMethods(
                HookMethodCollection::defaultBefore()->add(new HookMethod('failingHook', 100)),
            ),
            $emitter,
        );
    }

    public function testEmitsCalledAndFinishedAndRethrowsForSkippedTestWithoutErroredEvent(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodCalled');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodFinished')
            ->seal();

        $this->expectException(SkippedWithMessageException::class);
        $this->expectExceptionMessage('hook skipped');

        HookMethodInvoker::invokeBeforeTest(
            new HookFixture('testOne'),
            $this->hookMethods(
                HookMethodCollection::defaultBefore()->add(new HookMethod('skippingHook', 100)),
            ),
            $emitter,
        );
    }

    public function testStopsInvokingFurtherHooksAfterUnexpectedException(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodCalled');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodErrored');

        $emitter
            ->expects($this->once())
            ->method('beforeTestMethodFinished')
            ->seal();

        $this->expectException(Exception::class);

        HookMethodInvoker::invokeBeforeTest(
            new HookFixture('testOne'),
            $this->hookMethods(
                HookMethodCollection::defaultBefore()
                    ->add(new HookMethod('throwingHook', 100))
                    ->add(new HookMethod('secondSuccessfulHook', 50)),
            ),
            $emitter,
        );
    }

    /**
     * @return array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}
     */
    private function hookMethods(HookMethodCollection $before): array
    {
        return [
            'beforeClass'   => HookMethodCollection::defaultBeforeClass(),
            'before'        => $before,
            'preCondition'  => HookMethodCollection::defaultPreCondition(),
            'postCondition' => HookMethodCollection::defaultPostCondition(),
            'after'         => HookMethodCollection::defaultAfter(),
            'afterClass'    => HookMethodCollection::defaultAfterClass(),
        ];
    }
}
