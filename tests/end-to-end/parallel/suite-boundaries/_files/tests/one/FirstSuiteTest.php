<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelSuiteBoundaries;

use function file_put_contents;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\TestCase;

final class FirstSuiteTest extends TestCase
{
    public function testFinishesBeforeTheSecondSuiteStarts(): void
    {
        // Keep this test running long enough that a test of the second suite
        // that was started alongside it would look for the marker before it
        // has been written.
        usleep(500000);

        file_put_contents(
            sys_get_temp_dir() . '/phpunit-parallel-suite-boundaries.marker',
            'first suite finished',
        );

        $this->assertTrue(true);
    }
}
