<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\StopOnFailure;

use function clearstatcache;
use function filesize;
use function getenv;
use function usleep;
use PHPUnit\Framework\TestCase;

final class AFailsOnceTheOtherTestHasStartedTest extends TestCase
{
    public function testFails(): void
    {
        // The test of the other class must be running when the run stops,
        // so this test waits for it to have started, for ten seconds at
        // most, before it fails.
        $marker = (string) getenv('PHPUNIT_TEST_WORKER_STOP_MARKER');

        for ($waited = 0; $waited < 1000; $waited++) {
            clearstatcache();

            if (filesize($marker) > 0) {
                break;
            }

            usleep(10000);
        }

        $this->fail('failure');
    }
}
