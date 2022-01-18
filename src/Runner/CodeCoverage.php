<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverage
{
    private static ?\SebastianBergmann\CodeCoverage\CodeCoverage $instance = null;
    private static ?Driver $driver                                         = null;
    private static bool $collecting                                        = false;

    /**
     * @throws Exception
     */
    public static function activate(Filter $filter, bool $pathCoverage): void
    {
        self::ensureIsNotActive();

        if ($pathCoverage) {
            self::$driver = (new Selector)->forLineAndPathCoverage($filter);
        } else {
            self::$driver = (new Selector)->forLineCoverage($filter);
        }

        self::$instance = new \SebastianBergmann\CodeCoverage\CodeCoverage(
            self::$driver,
            $filter
        );
    }

    /**
     * @throws Exception
     */
    public static function instance(): \SebastianBergmann\CodeCoverage\CodeCoverage
    {
        self::ensureIsActive();

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public static function driver(): Driver
    {
        self::ensureIsActive();

        return self::$driver;
    }

    /**
     * @throws Exception
     */
    public static function start(TestCase $test): void
    {
        self::ensureIsActive();

        if (self::$collecting) {
            return;
        }

        self::$collecting = true;

        self::$instance->start($test);
    }

    /**
     * @throws Exception
     */
    public static function stop(bool $append = true, array|false $linesToBeCovered = [], array $linesToBeUsed = []): void
    {
        self::ensureIsActive();

        if (!self::$collecting) {
            return;
        }

        /* @noinspection UnusedFunctionResultInspection */
        self::$instance->stop($append, $linesToBeCovered, $linesToBeUsed);

        self::$collecting = false;
    }

    public static function deactivate(): void
    {
        self::$driver   = null;
        self::$instance = null;
    }

    public static function isActive(): bool
    {
        return self::$instance !== null;
    }

    /**
     * @throws Exception
     */
    private static function ensureIsActive(): void
    {
        if (self::$instance === null) {
            throw new Exception(
                'Code Coverage has not been set up'
            );
        }
    }

    /**
     * @throws Exception
     */
    private static function ensureIsNotActive(): void
    {
        if (self::$instance !== null) {
            throw new Exception(
                'Code Coverage has not been set up'
            );
        }
    }
}
