#!/usr/bin/env php
<?php declare(strict_types=1);

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;

require __DIR__ . '/../vendor/autoload.php';

/** @var string[] $lines */
$lines = \file(__DIR__ . '/../src/Framework/Assert.php');

$buffer = '<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtIndex as InvokedAtIndexMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls as ConsecutiveCallsStub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument as ReturnArgumentStub;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback as ReturnCallbackStub;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf as ReturnSelfStub;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap as ReturnValueMapStub;
';

$usedClasses = [];

$class = new ReflectionClass(Assert::class);

$constraintMethods = '';

foreach ($class->getMethods() as $method) {
    if (!$method->hasReturnType() || $method->getReturnType()->isBuiltin()) {
        continue;
    }

    $returnType = new ReflectionClass($method->getReturnType()->getName());

    if (!$returnType->isSubclassOf(Constraint::class)) {
        continue;
    }

    $usedClasses[] = $returnType->getName();

    $constraintMethods .= \sprintf(
        "%s\n{\n    return Assert::%s(...\\func_get_args());\n}\n\n",
        \str_replace('public static ', '', \trim($lines[$method->getStartLine() - 1])),
        $method->getName()
    );
}

$usedClasses = \array_unique($usedClasses);
\sort($usedClasses);

foreach ($usedClasses as $usedClass) {
    $buffer .= \sprintf(
        "use %s;\n",
        $usedClass
    );
}

$buffer .= "\n";

foreach ($class->getMethods() as $method) {
    if (\strpos($method->getName(), 'assert') !== 0) {
        continue;
    }

    $buffer .= \sprintf(
        "%s\n%s\n{\n    Assert::%s(...\\func_get_args());\n}\n\n",
        \str_replace('     *', ' *', $method->getDocComment()),
        \str_replace('public static ', '', \trim($lines[$method->getStartLine() - 1])),
        $method->getName()
    );
}

$buffer .= $constraintMethods;

$buffer .= '/**
 * Returns a matcher that matches when the method is executed
 * zero or more times.
 */
function any(): AnyInvokedCountMatcher
{
    return new AnyInvokedCountMatcher;
}

/**
 * Returns a matcher that matches when the method is never executed.
 */
function never(): InvokedCountMatcher
{
    return new InvokedCountMatcher(0);
}

/**
 * Returns a matcher that matches when the method is executed
 * at least N times.
 *
 * @param int $requiredInvocations
 */
function atLeast($requiredInvocations): InvokedAtLeastCountMatcher
{
    return new InvokedAtLeastCountMatcher(
        $requiredInvocations
    );
}

/**
 * Returns a matcher that matches when the method is executed at least once.
 */
function atLeastOnce(): InvokedAtLeastOnceMatcher
{
    return new InvokedAtLeastOnceMatcher;
}

/**
 * Returns a matcher that matches when the method is executed exactly once.
 */
function once(): InvokedCountMatcher
{
    return new InvokedCountMatcher(1);
}

/**
 * Returns a matcher that matches when the method is executed
 * exactly $count times.
 *
 * @param int $count
 */
function exactly($count): InvokedCountMatcher
{
    return new InvokedCountMatcher($count);
}

/**
 * Returns a matcher that matches when the method is executed
 * at most N times.
 *
 * @param int $allowedInvocations
 */
function atMost($allowedInvocations): InvokedAtMostCountMatcher
{
    return new InvokedAtMostCountMatcher($allowedInvocations);
}

/**
 * Returns a matcher that matches when the method is executed
 * at the given index.
 *
 * @param int $index
 */
function at($index): InvokedAtIndexMatcher
{
    return new InvokedAtIndexMatcher($index);
}

/**
 * @param mixed $value
 */
function returnValue($value): ReturnStub
{
    return new ReturnStub($value);
}

/**
 * @param array $valueMap
 */
function returnValueMap(array $valueMap): ReturnValueMapStub
{
    return new ReturnValueMapStub($valueMap);
}

/**
 * @param int $argumentIndex
 */
function returnArgument($argumentIndex): ReturnArgumentStub
{
    return new ReturnArgumentStub($argumentIndex);
}

/**
 * @param mixed $callback
 */
function returnCallback($callback): ReturnCallbackStub
{
    return new ReturnCallbackStub($callback);
}

/**
 * Returns the current object.
 *
 * This method is useful when mocking a fluent interface.
 */
function returnSelf(): ReturnSelfStub
{
    return new ReturnSelfStub;
}

/**
 * @param Throwable $exception
 */
function throwException(Throwable $exception): ExceptionStub
{
    return new ExceptionStub($exception);
}

/**
 * @param mixed $value , ...
 */
function onConsecutiveCalls(): ConsecutiveCallsStub
{
    $args = \func_get_args();

    return new ConsecutiveCallsStub($args);
}
';

\file_put_contents(__DIR__ . '/../src/Framework/Assert/Functions.php', $buffer);
