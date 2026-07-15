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

use function file_put_contents;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\TestCase;

final class SlowTest extends TestCase
{
    public function testThatIsNeverReported(): void
    {
        // This test runs in a worker alongside the failing test of the other
        // class. The run stops as soon as the failure is reported, and this
        // test's worker is terminated mid-sleep — so the marker file must
        // never come into existence.
        usleep(3000000);

        file_put_contents(
            sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure.marker',
            'the run was not stopped early',
        );

        $this->assertTrue(true);
    }
}
