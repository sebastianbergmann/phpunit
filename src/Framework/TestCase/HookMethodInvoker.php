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

use PHPUnit\Event;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Runner\HookMethodCollection;
use PHPUnit\Util\Reflection;
use ReflectionClass;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-import-type HookMethodsByType from HookMethods
 */
final readonly class HookMethodInvoker
{
    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    public static function invokeBeforeClass(TestCase $test, array $hookMethods): void
    {
        self::invokeWithoutEmittingEvents($test, $hookMethods['beforeClass']);
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     */
    public static function invokeBeforeTest(TestCase $test, array $hookMethods, Event\Emitter $emitter): void
    {
        self::invokeMethodLevel(
            $test,
            $hookMethods['before'],
            $emitter,
            'beforeTestMethodCalled',
            'beforeTestMethodErrored',
            'beforeTestMethodFailed',
            'beforeTestMethodFinished',
        );
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     */
    public static function invokePreCondition(TestCase $test, array $hookMethods, Event\Emitter $emitter): void
    {
        self::invokeMethodLevel(
            $test,
            $hookMethods['preCondition'],
            $emitter,
            'preConditionCalled',
            'preConditionErrored',
            'preConditionFailed',
            'preConditionFinished',
        );
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     */
    public static function invokePostCondition(TestCase $test, array $hookMethods, Event\Emitter $emitter): void
    {
        self::invokeMethodLevel(
            $test,
            $hookMethods['postCondition'],
            $emitter,
            'postConditionCalled',
            'postConditionErrored',
            'postConditionFailed',
            'postConditionFinished',
        );
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     */
    public static function invokeAfterTest(TestCase $test, array $hookMethods, Event\Emitter $emitter): void
    {
        self::invokeMethodLevel(
            $test,
            $hookMethods['after'],
            $emitter,
            'afterTestMethodCalled',
            'afterTestMethodErrored',
            'afterTestMethodFailed',
            'afterTestMethodFinished',
        );
    }

    /**
     * @param HookMethodsByType $hookMethods
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    public static function invokeAfterClass(TestCase $test, array $hookMethods): void
    {
        self::invokeWithoutEmittingEvents($test, $hookMethods['afterClass']);
    }

    /**
     * @param 'afterTestMethodCalled'|'beforeTestMethodCalled'|'postConditionCalled'|'preConditionCalled'         $calledMethod
     * @param 'afterTestMethodErrored'|'beforeTestMethodErrored'|'postConditionErrored'|'preConditionErrored'     $erroredMethod
     * @param 'afterTestMethodFailed'|'beforeTestMethodFailed'|'postConditionFailed'|'preConditionFailed'         $failedMethod
     * @param 'afterTestMethodFinished'|'beforeTestMethodFinished'|'postConditionFinished'|'preConditionFinished' $finishedMethod
     *
     * @throws Throwable
     */
    private static function invokeMethodLevel(TestCase $test, HookMethodCollection $hookMethods, Event\Emitter $emitter, string $calledMethod, string $erroredMethod, string $failedMethod, string $finishedMethod): void
    {
        $eventTest      = $test->valueObjectForEvents();
        $reflector      = new ReflectionClass($test::class);
        $methodsInvoked = [];

        foreach ($hookMethods->methodNamesSortedByPriority() as $methodName) {
            if (Reflection::methodDoesNotExistOrIsDeclaredInTestCase($reflector, $methodName)) {
                continue;
            }

            $methodInvoked = new Event\Code\ClassMethod(
                $test::class,
                $methodName,
            );

            $hookMethod = $reflector->getMethod($methodName);

            try {
                if ($hookMethod->isStatic()) {
                    $hookMethod->invoke(null);
                } else {
                    $hookMethod->invoke($test);
                }
            } catch (Throwable $t) {
            }

            /** @phpstan-ignore method.dynamicName */
            $emitter->{$calledMethod}(
                $eventTest,
                $methodInvoked
            );

            $methodsInvoked[] = $methodInvoked;

            if (isset($t)) {
                if (!$t instanceof SkippedTest) {
                    if ($t instanceof AssertionFailedError) {
                        $method = $failedMethod;
                    } else {
                        $method = $erroredMethod;
                    }

                    /** @phpstan-ignore method.dynamicName */
                    $emitter->{$method}(
                        $eventTest,
                        $methodInvoked,
                        Event\Code\ThrowableBuilder::from($t),
                    );
                }

                break;
            }
        }

        if ($methodsInvoked !== []) {
            /** @phpstan-ignore method.dynamicName */
            $emitter->{$finishedMethod}(
                $eventTest,
                ...$methodsInvoked
            );
        }

        if (isset($t)) {
            throw $t;
        }
    }

    /**
     * The before-first-test methods and after-last-test methods of a test that
     * runs in process isolation are called again in the child process, as part
     * of running that test. The test runner calls them for the test class in
     * its own process and reports that it does. In the child process, they are
     * part of the test, so their outcome is the outcome of the test.
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    private static function invokeWithoutEmittingEvents(TestCase $test, HookMethodCollection $hookMethods): void
    {
        $reflector = new ReflectionClass($test::class);

        foreach ($hookMethods->methodNamesSortedByPriority() as $methodName) {
            if (Reflection::methodDoesNotExistOrIsDeclaredInTestCase($reflector, $methodName)) {
                continue;
            }

            $hookMethod = $reflector->getMethod($methodName);

            if ($hookMethod->isStatic()) {
                $hookMethod->invoke(null);
            } else {
                $hookMethod->invoke($test);
            }
        }
    }
}
