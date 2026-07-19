<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelExclusivity;

use function file_put_contents;
use function microtime;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\TestCase;

final class SlowThirdTest extends TestCase
{
    public function testThatRecordsWhenItRan(): void
    {
        $start = microtime(true);

        usleep(400000);

        file_put_contents(
            sys_get_temp_dir() . '/phpunit-parallel-exclusivity-third.interval',
            $start . ' ' . microtime(true),
        );

        $this->assertTrue(true);
    }
}
