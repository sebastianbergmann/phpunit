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
use ReflectionObject;
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
    public static function invokeBeforeClass(TestCase $test, array $hookMethods, Event\Emitter $emitter): void
    {
        self::invokeClassLevel(
            $test,
            $hookMethods['beforeClass'],
            $emitter,
            'beforeFirstTestMethodCalled',
            'beforeFirstTestMethodErrored',
            'beforeFirstTestMethodFailed',
            'beforeFirstTestMethodFinished',
        );
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
    public static function invokeAfterClass(TestCase $test, array $hookMethods, Event\Emitter $emitter): void
    {
        self::invokeClassLevel(
            $test,
            $hookMethods['afterClass'],
            $emitter,
            'afterLastTestMethodCalled',
            'afterLastTestMethodErrored',
            'afterLastTestMethodFailed',
            'afterLastTestMethodFinished',
        );
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
        self::doInvoke($test, $hookMethods, $emitter, $test->valueObjectForEvents(), $calledMethod, $erroredMethod, $failedMethod, $finishedMethod);
    }

    /**
     * @param 'afterLastTestMethodCalled'|'beforeFirstTestMethodCalled'     $calledMethod
     * @param 'afterLastTestMethodErrored'|'beforeFirstTestMethodErrored'   $erroredMethod
     * @param 'afterLastTestMethodFailed'|'beforeFirstTestMethodFailed'     $failedMethod
     * @param 'afterLastTestMethodFinished'|'beforeFirstTestMethodFinished' $finishedMethod
     *
     * @throws Throwable
     *
     * @codeCoverageIgnore
     */
    private static function invokeClassLevel(TestCase $test, HookMethodCollection $hookMethods, Event\Emitter $emitter, string $calledMethod, string $erroredMethod, string $failedMethod, string $finishedMethod): void
    {
        self::doInvoke($test, $hookMethods, $emitter, $test::class, $calledMethod, $erroredMethod, $failedMethod, $finishedMethod);
    }

    /**
     * @param class-string<TestCase>|Event\Code\TestMethod $eventTest
     *
     * @throws Throwable
     */
    private static function doInvoke(TestCase $test, HookMethodCollection $hookMethods, Event\Emitter $emitter, Event\Code\TestMethod|string $eventTest, string $calledMethod, string $erroredMethod, string $failedMethod, string $finishedMethod): void
    {
        $reflector      = new ReflectionObject($test);
        $methodsInvoked = [];

        foreach ($hookMethods->methodNamesSortedByPriority() as $methodName) {
            if (self::methodDoesNotExistOrIsDeclaredInTestCase($reflector, $methodName)) {
                continue;
            }

            $methodInvoked = new Event\Code\ClassMethod(
                $test::class,
                $methodName,
            );

            try {
                $reflector->getMethod($methodName)->invoke($test);
            } catch (Throwable $t) {
            }

            /** @phpstan-ignore method.dynamicName */
            $emitter->{$calledMethod}(
                $eventTest,
                $methodInvoked
            );

            $methodsInvoked[] = $methodInvoked;

            if (isset($t) && !$t instanceof SkippedTest) {
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
     * @param non-empty-string $methodName
     */
    private static function methodDoesNotExistOrIsDeclaredInTestCase(ReflectionObject $reflector, string $methodName): bool
    {
        return !$reflector->hasMethod($methodName) ||
               $reflector->getMethod($methodName)->getDeclaringClass()->getName() === TestCase::class;
    }
}
