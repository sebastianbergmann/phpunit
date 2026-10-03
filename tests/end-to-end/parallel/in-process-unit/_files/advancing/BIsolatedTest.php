<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelInProcessUnit;

use function clearstatcache;
use function file_put_contents;
use function getenv;
use function is_file;
use function microtime;
use function unlink;
use function usleep;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Runs in the main process, which waits for the child process of the test.
 * The file that the test waits for is created by CMeanwhileTest, which a
 * worker runs only if the main process advances the workers while it waits.
 */
#[RunTestsInSeparateProcesses]
final class BIsolatedTest extends TestCase
{
    public function testWaitsForATestThatAWorkerRunsMeanwhile(): void
    {
        $handshake = getenv('PHPUNIT_TEST_HANDSHAKE');

        $this->assertIsString($handshake);

        file_put_contents($handshake . '.started', '');

        $deadline = microtime(true) + 10;

        while (!is_file($handshake . '.created') && microtime(true) < $deadline) {
            usleep(10000);

            clearstatcache();
        }

        $this->assertFileExists($handshake . '.created');

        unlink($handshake . '.created');
    }
}
