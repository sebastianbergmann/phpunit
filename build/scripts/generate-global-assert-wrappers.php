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

use ArrayAccess;
use Countable;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
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
use PHPUnit\Util\Xml\XmlException;
';

$usedClasses = [];

$class = new ReflectionClass(Assert::class);

$constraintMethods = '';

foreach ($class->getMethods() as $method) {
    $returnType = $method->getReturnType();

    assert($returnType instanceof ReflectionNamedType || $returnType instanceof ReflectionUnionType);

    if ($returnType instanceof ReflectionNamedType && $returnType->isBuiltin()) {
        continue;
    }

    $returnType = new ReflectionClass($returnType->getName());

    if (!$returnType->isSubclassOf(Constraint::class)) {
        continue;
    }

    $usedClasses[] = $returnType->getName();

    // skip, so we can later on append a signature including precise analysis types
    if ($method->getName() === 'callback') {
        continue;
    }

    $constraintMethods .= \sprintf(
        "if (!function_exists('PHPUnit\Framework\\" . $method->getName() . "')) {\n%s\n{\n    return Assert::%s(...\\func_get_args());\n}\n}\n\n",
        \str_replace('final public static ', '', \trim($lines[$method->getStartLine() - 1])),
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
        ["*\n * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit\n * @see Assert::" . $method->getName() . "\n */", ' *'],
        (string) $method->getDocComment()
    );

    $signature = \str_replace('final public static ', '', \trim($lines[$method->getStartLine() - 1]));
    $body      = "{\n    Assert::" . $method->getName() . "(...\\func_get_args());\n}";

    $buffer .= "if (!function_exists('PHPUnit\Framework\\" . $method->getName() . "')) {\n";
    $buffer .= "$docComment\n$signature\n$body\n";
    $buffer .= "}\n\n";
}

$buffer .= $constraintMethods;

$buffer .= <<<'EOT'
if (!function_exists('PHPUnit\Framework\callback')) {
    /**
     * @template CallbackInput of mixed
     *
     * @param callable(CallbackInput $callback): bool $callback
     *
     * @return Callback<CallbackInput>
     */
    function callback(callable $callback): Callback
    {
        return Assert::callback($callback);
    }
}

if (!function_exists('PHPUnit\Framework\any')) {
    /**
     * Returns a matcher that matches when the method is executed
     * zero or more times.
     */
    function any(): AnyInvokedCountMatcher
    {
        return new AnyInvokedCountMatcher;
    }
}

if (!function_exists('PHPUnit\Framework\never')) {
    /**
     * Returns a matcher that matches when the method is never executed.
     */
    function never(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(0);
    }
}

if (!function_exists('PHPUnit\Framework\atLeast')) {
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
}

if (!function_exists('PHPUnit\Framework\atLeastOnce')) {
    /**
     * Returns a matcher that matches when the method is executed at least once.
     */
    function atLeastOnce(): InvokedAtLeastOnceMatcher
    {
        return new InvokedAtLeastOnceMatcher;
    }
}

if (!function_exists('PHPUnit\Framework\once')) {
    /**
     * Returns a matcher that matches when the method is executed exactly once.
     */
    function once(): InvokedCountMatcher
    {
        return new InvokedCountMatcher(1);
    }
}

if (!function_exists('PHPUnit\Framework\exactly')) {
    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     */
    function exactly(int $count): InvokedCountMatcher
    {
        return new InvokedCountMatcher($count);
    }
}

if (!function_exists('PHPUnit\Framework\atMost')) {
    /**
     * Returns a matcher that matches when the method is executed
     * at most N times.
     */
    function atMost(int $allowedInvocations): InvokedAtMostCountMatcher
    {
        return new InvokedAtMostCountMatcher($allowedInvocations);
    }
}

if (!function_exists('PHPUnit\Framework\returnValue')) {
    function returnValue(mixed $value): ReturnStub
    {
        return new ReturnStub($value);
    }
}

if (!function_exists('PHPUnit\Framework\returnValueMap')) {
    /**
     * @param array<mixed> $valueMap
     */
    function returnValueMap(array $valueMap): ReturnValueMapStub
    {
        return new ReturnValueMapStub($valueMap);
    }
}

if (!function_exists('PHPUnit\Framework\returnArgument')) {
    function returnArgument(int $argumentIndex): ReturnArgumentStub
    {
        return new ReturnArgumentStub($argumentIndex);
    }
}

if (!function_exists('PHPUnit\Framework\returnCallback')) {
    function returnCallback(callable $callback): ReturnCallbackStub
    {
        return new ReturnCallbackStub($callback);
    }
}

if (!function_exists('PHPUnit\Framework\returnSelf')) {
    /**
     * Returns the current object.
     *
     * This method is useful when mocking a fluent interface.
     */
    function returnSelf(): ReturnSelfStub
    {
        return new ReturnSelfStub;
    }
}

if (!function_exists('PHPUnit\Framework\throwException')) {
    function throwException(\Throwable $exception): ExceptionStub
    {
        return new ExceptionStub($exception);
    }
}

if (!function_exists('PHPUnit\Framework\onConsecutiveCalls')) {
    /**
     * @param mixed $value , ...
     */
    function onConsecutiveCalls(): ConsecutiveCallsStub
    {
        $arguments = \func_get_args();

        return new ConsecutiveCallsStub($arguments);
    }
}

EOT;

\file_put_contents(__DIR__ . '/../../src/Framework/Assert/Functions.php', $buffer);
