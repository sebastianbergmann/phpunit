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
use function getenv;
use function is_file;
use function microtime;
use function unlink;
use function usleep;
use PHPUnit\Framework\TestCase;

/**
 * Keeps its worker busy until BIsolatedTest has started, so that
 * CMeanwhileTest can only be run by the worker that is freed while
 * BIsolatedTest waits.
 */
final class DOccupyingTest extends TestCase
{
    public function testKeepsItsWorkerBusyUntilTheIsolatedTestHasStarted(): void
    {
        $handshake = getenv('PHPUNIT_TEST_HANDSHAKE');

        $this->assertIsString($handshake);

        $deadline = microtime(true) + 10;

        while (!is_file($handshake . '.started') && microtime(true) < $deadline) {
            usleep(10000);

            clearstatcache();
        }

        $this->assertFileExists($handshake . '.started');

        unlink($handshake . '.started');
    }
}
