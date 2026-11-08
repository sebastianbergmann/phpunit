<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Falsification;

use Error;
use ReflectionProperty;

/**
 * Creates errors like the ones that are raised when a recursion reaches a
 * limit, with stack traces whose frames are calls in lines of this file.
 */
final class RecursionLimit
{
    public const string CALL_STACK_LIMIT     = 'Maximum call stack size of 8339456 bytes (zend.max_allowed_stack_size - zend.reserved_stack_size) reached. Infinite recursion?';
    public const string XDEBUG_NESTING_LIMIT = "Xdebug has detected a possible infinite loop, and aborted your script with a stack depth of '512' frames";

    /**
     * Returns an error whose stack trace has, from the innermost frame
     * outward, twenty repetitions of the calls in the given lines of an
     * unbounded recursion, the calls in line 10 of a finite recursion of the
     * given depth that led to it, and the call in line 5 that started the
     * finite recursion.
     *
     * The error is raised in the given line, which is not a call of the
     * recursion, as Xdebug raises it in the declaration of the function whose
     * call exceeds its limit, or, when no line is given, by the innermost call
     * of the recursion, as PHP raises it.
     *
     * @param non-empty-list<positive-int> $cycle
     * @param non-negative-int             $depthOfFiniteRecursion
     * @param ?positive-int                $line
     */
    public static function inRecursion(string $message, array $cycle, int $depthOfFiniteRecursion, ?int $line = null): Error
    {
        $lines = [];

        for ($i = 0; $i < 20; $i++) {
            foreach ($cycle as $call) {
                $lines[] = $call;
            }
        }

        for ($i = 0; $i < $depthOfFiniteRecursion; $i++) {
            $lines[] = 10;
        }

        $lines[] = 5;

        if ($line === null) {
            $line = $cycle[0];
        }

        return self::withStackTrace($message, $lines, $line);
    }

    /**
     * Returns an error that is raised in the given line and whose stack trace
     * has calls in the given lines, from the innermost frame outward.
     *
     * @param non-empty-list<positive-int> $lines
     * @param positive-int                 $line
     */
    public static function withStackTrace(string $message, array $lines, int $line): Error
    {
        $trace = [];

        foreach ($lines as $call) {
            $trace[] = ['file' => __FILE__, 'line' => $call, 'function' => 'call'];
        }

        $error = new Error($message);

        new ReflectionProperty(Error::class, 'line')->setValue($error, $line);
        new ReflectionProperty(Error::class, 'trace')->setValue($error, $trace);

        return $error;
    }
}
