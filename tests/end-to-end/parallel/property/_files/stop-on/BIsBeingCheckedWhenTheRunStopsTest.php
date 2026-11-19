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

use const FILE_APPEND;
use function file_put_contents;
use function sys_get_temp_dir;
use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class BIsBeingCheckedWhenTheRunStopsTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        self::mark("tearDownAfterClass\n");
    }

    /**
     * Checking this property for all of its examples takes longer than the
     * test of the other class waits before it fails.
     */
    #[Property(300)]
    public function testIsBeingCheckedWhenTheRunStops(int $value): void
    {
        self::mark('.');

        usleep(100000);

        $this->assertIsInt($value);
    }

    private static function mark(string $what): void
    {
        file_put_contents(
            sys_get_temp_dir() . '/phpunit-parallel-property-stop-on-failure.marker',
            $what,
            FILE_APPEND,
        );
    }
}
