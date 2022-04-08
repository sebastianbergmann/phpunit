<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use PHPUnit\Event\Test\Failure;
use PHPUnit\Event\TestSuite\FailureCollection;
use PHPUnit\Event\TestSuite\Result;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TestResultMapper
{
    public function map(TestResult $result): Result
    {
        return new Result(
            $result->count(),
            self::toFailureCollection(...$result->errors()),
            self::toFailureCollection(...$result->failures()),
            self::toFailureCollection(...$result->notImplemented()),
            self::toFailureCollection(...$result->risky()),
            self::toFailureCollection(...$result->skipped()),
            self::toFailureCollection(...$result->warnings()),
            $result->passed(),
            $result->passedClasses()
        );
    }

    private static function toFailureCollection(TestFailure ...$testFailures): FailureCollection
    {
        return new FailureCollection(
            ...array_map(
                static function (TestFailure $testFailure): Failure
                {
                    return new Failure(
                        $testFailure->getTestName(),
                        $testFailure->thrownException()
                    );
                },
                $testFailures
            )
        );
    }
}
