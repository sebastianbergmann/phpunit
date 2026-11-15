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
use function register_shutdown_function;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\TestCase;

/**
 * The test classes that extend this one are run by one worker process each.
 * What happens in a worker process is written to a file of its test class,
 * as the test that drives them cannot ask the operating system whether a
 * worker process has ended in a way that works everywhere.
 */
abstract class MainProcessIsKilledTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $marker = static::prefix() . '.marker';

        register_shutdown_function(
            static function () use ($marker): void
            {
                file_put_contents($marker, "worker process ended\n", FILE_APPEND);
            },
        );
    }

    public static function tearDownAfterClass(): void
    {
        static::mark('tearDownAfterClass');
    }

    public function testIsRunningWhenTheMainProcessIsKilled(): void
    {
        // The test that drives this one kills the main process once it knows
        // which worker processes run the test classes, and says so. This
        // test waits for that, for ten seconds at most, before it finishes.
        file_put_contents(static::prefix() . '.pid', (string) getmypid());

        for ($waited = 0; $waited < 1000 && !is_file(sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone.killed'); $waited++) {
            usleep(10000);
        }

        static::mark('testIsRunningWhenTheMainProcessIsKilled');

        $this->assertTrue(true);
    }

    public function testIsNotRunOnceTheMainProcessIsGone(): void
    {
        static::mark('testIsNotRunOnceTheMainProcessIsGone');

        $this->assertTrue(true);
    }

    /**
     * @return non-empty-string
     */
    abstract protected static function nameOfWorker(): string;

    private static function mark(string $what): void
    {
        file_put_contents(static::prefix() . '.marker', $what . "\n", FILE_APPEND);
    }

    private static function prefix(): string
    {
        return sys_get_temp_dir() . '/phpunit-parallel-main-process-is-gone.' . static::nameOfWorker();
    }
}
