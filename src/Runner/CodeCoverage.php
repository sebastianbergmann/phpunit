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
use SebastianBergmann\CodeCoverage\Test\TestSize\TestSize;
use SebastianBergmann\CodeCoverage\Test\TestStatus\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverage
{
    private static ?\SebastianBergmann\CodeCoverage\CodeCoverage $instance = null;
    private static ?Driver $driver                                         = null;
    private static bool $collecting                                        = false;
    private static ?TestCase $test                                         = null;

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

        $size = TestSize::unknown();

        if ($test->size()->isSmall()) {
            $size = TestSize::small();
        } elseif ($test->size()->isMedium()) {
            $size = TestSize::medium();
        } elseif ($test->size()->isLarge()) {
            $size = TestSize::large();
        }

        self::$test = $test;

        self::$instance->start(
            $test->valueObjectForEvents()->id(),
            $size
        );

        self::$collecting = true;
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

        $status = TestStatus::unknown();

        if (self::$test !== null) {
            if (self::$test->status()->isSuccess()) {
                $status = TestStatus::success();
            } else {
                $status = TestStatus::failure();
            }
        }

        /* @noinspection UnusedFunctionResultInspection */
        self::$instance->stop($append, $status, $linesToBeCovered, $linesToBeUsed);

        self::$test       = null;
        self::$collecting = false;
    }

    public static function deactivate(): void
    {
        self::$driver   = null;
        self::$instance = null;
        self::$test     = null;
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
            throw new CodeCoverageIsNotActiveException;
        }
    }

    /**
     * @throws Exception
     */
    private static function ensureIsNotActive(): void
    {
        if (self::$instance !== null) {
            throw new CodeCoverageIsActiveException;
        }
    }
}
