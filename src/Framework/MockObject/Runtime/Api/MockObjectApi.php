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
use PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMockerBuilder;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait MockObjectApi
{
    private readonly MockObjectInternalState $__phpunit_mockObjectInternalState;

    public function __phpunit_initMockObjectInternalState(): void
    {
        $this->__phpunit_mockObjectInternalState = new MockObjectInternalState;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_hasMatchers(): bool
    {
        return $this->__phpunit_getInvocationHandler()->hasMatchers();
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_setOriginalObject(object $originalObject): void
    {
        $this->__phpunit_mockObjectInternalState->setOriginalObject($originalObject);
    }

    /** @noinspection MagicMethodsValidityInspection */
    private function __phpunit_getOriginalObject(): ?object
    {
        return $this->__phpunit_mockObjectInternalState->getOriginalObject();
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_verify(bool $unsetInvocationMocker = true): void
    {
        $this->__phpunit_getInvocationHandler()->verify();

        if ($unsetInvocationMocker) {
            $this->__phpunit_unsetInvocationMocker();
        }
    }

    abstract public function __phpunit_getInvocationHandler(): InvocationHandler;

    abstract public function __phpunit_unsetInvocationMocker(): void;

    public function expects(InvocationOrder $matcher): InvocationMockerBuilder
    {
        assert($this instanceof StubInternal);

        if (!$this->__phpunit_wasGeneratedAsMockObject()) {
            $message = 'Configuring expectations on test doubles that were created as test stubs is deprecated. Support for this will be removed in PHPUnit 12.';

            try {
                $test = TestMethodBuilder::fromCallStack();

                if (!MockObjectInternalState::issetDeprecationEmittedForTest(self::class, $test->id())) {
                    EventFacade::emitter()->testTriggeredPhpunitDeprecation(
                        $test,
                        $message,
                    );

                    MockObjectInternalState::setDeprecationEmittedForTest(self::class, $test->id());
                }
            } catch (NoTestCaseObjectOnCallStackException) {
                EventFacade::emitter()->testRunnerTriggeredDeprecation($message);
            }
        }

        return $this->__phpunit_getInvocationHandler()->expects($matcher);
    }
}
