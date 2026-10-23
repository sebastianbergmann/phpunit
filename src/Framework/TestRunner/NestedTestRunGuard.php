<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use PHPUnit\Framework\NestedTestRunException;

/**
 * A test that is run while another test is running would start before the
 * other test is finished. The events of both tests would then not be
 * bracketed by the events that start and finish each test.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class NestedTestRunGuard
{
    private static bool $aTestIsRunning = false;

    /**
     * @throws NestedTestRunException
     */
    public static function testStarts(): void
    {
        if (self::$aTestIsRunning) {
            throw new NestedTestRunException;
        }

        self::$aTestIsRunning = true;
    }

    public static function testEnded(): void
    {
        self::$aTestIsRunning = false;
    }
}
