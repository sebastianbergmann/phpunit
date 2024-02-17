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
    private static array $__phpunit_deprecation_emitted_for_test = [];
    private object $__phpunit_originalObject;

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_hasMatchers(): bool
    {
        return $this->__phpunit_getInvocationHandler()->hasMatchers();
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_setOriginalObject(object $originalObject): void
    {
        $this->__phpunit_originalObject = $originalObject;
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
            $message = 'Expectations configured on test doubles that are created as test stubs are no longer verified since PHPUnit 10. Test doubles that are created as test stubs will no longer have the expects() method in PHPUnit 12. Update your test code to use createMock() instead of createStub(), for example.';

            try {
                $test = TestMethodBuilder::fromCallStack();

                if (!isset(self::$__phpunit_deprecation_emitted_for_test[$test->id()])) {
                    EventFacade::emitter()->testTriggeredPhpunitDeprecation(
                        $test,
                        $message,
                    );

                    self::$__phpunit_deprecation_emitted_for_test[$test->id()] = true;
                }
            } catch (NoTestCaseObjectOnCallStackException) {
                EventFacade::emitter()->testRunnerTriggeredDeprecation($message);
            }
        }

        return $this->__phpunit_getInvocationHandler()->expects($matcher);
    }
}
