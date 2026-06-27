<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

/**
 * Holds process-local state so that tests can assert whether they are executed
 * in the same process as a previous test.
 */
final class WorkerProcessProbe
{
    private static int $counter = 0;

    public static function increment(): int
    {
        self::$counter++;

        return self::$counter;
    }

    public static function value(): int
    {
        return self::$counter;
    }
}
