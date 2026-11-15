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

use function file_put_contents;
use function getenv;
use function usleep;
use PHPUnit\Framework\TestCase;

final class BIsRunningWhenTheRunStopsTest extends TestCase
{
    public function testIsRunningWhenTheRunStops(): void
    {
        file_put_contents((string) getenv('PHPUNIT_TEST_WORKER_STOP_MARKER'), 'started');

        usleep(1000000);

        $this->assertTrue(true);
    }
}
