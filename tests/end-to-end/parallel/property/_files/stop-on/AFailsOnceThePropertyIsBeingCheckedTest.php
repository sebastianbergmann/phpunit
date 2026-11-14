<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelProperty;

use function is_file;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\TestCase;

final class AFailsOnceThePropertyIsBeingCheckedTest extends TestCase
{
    public function testFails(): void
    {
        // The property of the other class must be being checked when the run
        // stops, so this test waits for its first example to have started,
        // for ten seconds at most, before it fails.
        for ($waited = 0; $waited < 1000 && !is_file(sys_get_temp_dir() . '/phpunit-parallel-property-stop-on-failure.marker'); $waited++) {
            usleep(10000);
        }

        $this->fail('failure');
    }
}
