#!/usr/bin/env php
<?php declare(strict_types=1);

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;

require __DIR__ . '/../../vendor/autoload.php';

/** @var string[] $lines */
$lines = \file(__DIR__ . '/../../src/Framework/Assert.php');

$buffer = '<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtIndex as InvokedAtIndexMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
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

    $docComment = \str_replace(
        ['*/', '     *'],
        ["*\n * @see Assert::" . $method->getName() . "\n */", ' *'],
        $method->getDocComment()
    );
    $signature = \str_replace('public static ', '', \trim($lines[$method->getStartLine() - 1]));
    $body      = "{\n    Assert::" . $method->getName() . "(...\\func_get_args());\n}";
    $buffer .= "$docComment\n$signature\n$body\n\n";
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
 */
function atLeast(int $requiredInvocations): InvokedAtLeastCountMatcher
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
 */
function exactly(int $count): InvokedCountMatcher
{
    return new InvokedCountMatcher($count);
}

/**
 * Returns a matcher that matches when the method is executed
 * at most N times.
 */
function atMost(int $allowedInvocations): InvokedAtMostCountMatcher
{
    return new InvokedAtMostCountMatcher($allowedInvocations);
}

/**
 * Returns a matcher that matches when the method is executed
 * at the given index.
 */
function at(int $index): InvokedAtIndexMatcher
{
    return new InvokedAtIndexMatcher($index);
}

function returnValue($value): ReturnStub
{
    return new ReturnStub($value);
}

function returnValueMap(array $valueMap): ReturnValueMapStub
{
    return new ReturnValueMapStub($valueMap);
}

function returnArgument(int $argumentIndex): ReturnArgumentStub
{
    return new ReturnArgumentStub($argumentIndex);
}

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

function throwException(\Throwable $exception): ExceptionStub
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

\file_put_contents(__DIR__ . '/../../src/Framework/Assert/Functions.php', $buffer);
