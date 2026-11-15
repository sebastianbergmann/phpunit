<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelMainProcessIsGone;

use const FILE_APPEND;
use function file_put_contents;
use function getmypid;
use function is_file;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\TestCase;

final class IsRunningWhenTheMainProcessIsKilledTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        self::mark('tearDownAfterClass');
    }

    public function testIsRunningWhenTheMainProcessIsKilled(): void
    {
        // The test that drives this one kills the main process once it knows
        // which worker process runs this test, and says so. This test waits
        // for that, for ten seconds at most, before it finishes.
        file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone.pid', (string) getmypid());

        for ($waited = 0; $waited < 1000 && !is_file(sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone.killed'); $waited++) {
            usleep(10000);
        }

        self::mark('testIsRunningWhenTheMainProcessIsKilled');

        $this->assertTrue(true);
    }

    public function testIsNotRunOnceTheMainProcessIsGone(): void
    {
        self::mark('testIsNotRunOnceTheMainProcessIsGone');

        $this->assertTrue(true);
    }

    private static function mark(string $what): void
    {
        file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone.marker', $what . "\n", FILE_APPEND);
    }
}
