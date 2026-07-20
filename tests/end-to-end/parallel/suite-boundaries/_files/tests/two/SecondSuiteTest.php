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

use function sys_get_temp_dir;
use PHPUnit\Framework\TestCase;

final class SecondSuiteTest extends TestCase
{
    public function testStartsAfterTheFirstSuiteHasFinished(): void
    {
        // The marker is written by the first suite's test just before it
        // finishes; tests of different top-level test suites must not run
        // alongside each other, so it must be there by now.
        $this->assertFileExists(sys_get_temp_dir() . '/phpunit-parallel-suite-boundaries.marker');
    }
}
