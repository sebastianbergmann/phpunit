<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelStopOn;

use const FILE_APPEND;
use function file_put_contents;
use function sleep;
use function sys_get_temp_dir;
use PHPUnit\Framework\TestCase;

final class BSleepsBeyondTheTimeLimitTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        self::mark('tearDownAfterClass');
    }

    public function testSleeps(): void
    {
        self::mark('testSleeps');

        sleep(10);

        $this->assertTrue(true);
    }

    private static function mark(string $what): void
    {
        file_put_contents(
            sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure-timeout.marker',
            $what . "\n",
            FILE_APPEND,
        );
    }
}
