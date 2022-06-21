<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Runner\Exception;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Collector $collector = null;

    public static function init(): void
    {
        if (self::$collector !== null) {
            return;
        }

        self::$collector = new Collector;
    }

    /**
     * @throws Exception
     */
    public static function result(): TestResult
    {
        self::ensureEventsAreCollected();

        return self::$collector->result();
    }

    /**
     * @psalm-return list<class-string>
     */
    public static function passedTestClasses(): array
    {
        self::ensureEventsAreCollected();

        return self::$collector->passedTestClasses();
    }

    /**
     * @psalm-return array<string,array{result: mixed, size: TestSize}>
     */
    public static function passedTestMethods(): array
    {
        self::ensureEventsAreCollected();

        return self::$collector->passedTestMethods();
    }

    /**
     * @throws Exception
     */
    private static function ensureEventsAreCollected(): void
    {
        if (self::$collector === null) {
            throw new Exception;
        }
    }
}
